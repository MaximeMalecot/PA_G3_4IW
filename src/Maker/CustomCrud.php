<?php

namespace App\Maker;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Renderer\FormTypeRenderer;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Validator\Validation;

#[When(env: 'dev')]
class CustomCrud extends AbstractMaker
{

    private Inflector $inflector;

    private DoctrineHelper $doctrineHelper;
    private FormTypeRenderer $formTypeRenderer;

    private string $controllerClassName;

    public function __construct(DoctrineHelper $doctrineHelper, FormTypeRenderer $formTypeRenderer)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->formTypeRenderer = $formTypeRenderer;
        $this->inflector = InflectorFactory::create()->build();
    }

    /**
     * @inheritDoc
     */
    public static function getCommandName(): string
    {
        return "make:custom-crud";
    }


    public static function getCommandDescription(): string
    {
        return "Creates CRUD in a back/front fashion.";
    }

    /**
     * @inheritDoc
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->addArgument('entity-class', InputArgument::OPTIONAL, sprintf('The class name of the entity to create CRUD (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->setHelp(file_get_contents(__DIR__ . '/Resources/help/CustomCrudHelp.txt'))
        ;

        $inputConfig->setArgumentAsNonInteractive('entity-class');
    }

    /**
     * @inheritDoc
     */
    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            Route::class,
            'router'
        );

        $dependencies->addClassDependency(
            AbstractType::class,
            'form'
        );

        $dependencies->addClassDependency(
            Validation::class,
            'validator'
        );

        $dependencies->addClassDependency(
            TwigBundle::class,
            'twig-bundle'
        );

        $dependencies->addClassDependency(
            DoctrineBundle::class,
            'orm'
        );

        $dependencies->addClassDependency(
            CsrfTokenManager::class,
            'security-csrf'
        );

        $dependencies->addClassDependency(
            ParamConverter::class,
            'annotations'
        );
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        if ($input->getArgument('entity-class') === null) {
            $argument = $command->getDefinition()->getArgument('entity-class');

            $entities = $this->doctrineHelper->getEntitiesForAutocomplete();

            $question = new Question($argument->getDescription());
            $question->setAutocompleterValues($entities);

            $value = $io->askQuestion($question);

            $input->setArgument('entity-class', $value);
        }

        $defaultControllerClass = Str::asClassName(sprintf('%s Controller', $input->getArgument('entity-class')));

        $this->controllerClassName = $io->ask(
            sprintf('Choose a name for your controller class (e.g. <fg=yellow>%s</>)', $defaultControllerClass),
            $defaultControllerClass
        );
    }

    /**
     * @inheritDoc
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        /*
         * 1. Récupérer le paramètre et vérifier qu'il s'agit d'une entité valide (optionnel: faire un wizard autocomplete si pas de paramètre passé)
         * 2. Créer les fichiers dans Controller/Back et Controller/Front; ainsi que dans templates/back et templates/front
         * 3. Remplir les variables avec le nom de l'entité passé en paramètre
         * */

        // ===================
        // ENTITY
        // ===================

        $entityClassDetails = $generator->createClassNameDetails(
            Validator::entityExists($input->getArgument('entity-class'), $this->doctrineHelper->getEntitiesForAutocomplete()),
            'Entity\\'
        );

        $entityDoctrineDetails = $this->doctrineHelper->createDoctrineDetails($entityClassDetails->getFullName());

        // ===================
        // REPOSITORY
        // ===================

        $repositoryVars = [];

        if ($entityDoctrineDetails->getRepositoryClass() !== null) {
            $repositoryClassDetails = $generator->createClassNameDetails(
                '\\'.$entityDoctrineDetails->getRepositoryClass(),
                'Repository\\',
                'Repository'
            );

            $repositoryVars = [
                'repository_full_class_name' => $repositoryClassDetails->getFullName(),
                'repository_class_name' => $repositoryClassDetails->getShortName(),
                'repository_var' => lcfirst($this->singularize($repositoryClassDetails->getShortName())),
            ];
        }

        // ===================
        // CONTROLLER
        // ===================


        $frontControllerClassDetails = $generator->createClassNameDetails(
            $this->controllerClassName,
            'Controller\\Front',
            'Controller'
        );

        $backControllerClassDetails = $generator->createClassNameDetails(
            $this->controllerClassName,
            'Controller\\Back',
            'Controller'
        );



        // ===================
        // FORMS
        // ===================

        $iter = 0;
        do {
            $formClassDetails = $generator->createClassNameDetails(
                $entityClassDetails->getRelativeNameWithoutSuffix().($iter ?: '').'Type',
                'Form\\',
                'Type'
            );
            ++$iter;
        } while (class_exists($formClassDetails->getFullName()));

        $entityVarPlural = lcfirst($this->pluralize($entityClassDetails->getShortName()));
        $entityVarSingular = lcfirst($this->singularize($entityClassDetails->getShortName()));

        $entityTwigVarPlural = Str::asTwigVariable($entityVarPlural);
        $entityTwigVarSingular = Str::asTwigVariable($entityVarSingular);

        $frontRouteName = "front_".Str::asRouteName($frontControllerClassDetails->getRelativeNameWithoutSuffix());
        $frontTemplatesPath = "front/" . Str::asFilePath($frontControllerClassDetails->getRelativeNameWithoutSuffix());

        $backRouteName = "back_".Str::asRouteName($backControllerClassDetails->getRelativeNameWithoutSuffix());
        $backTemplatesPath = "back/" .  Str::asFilePath($backControllerClassDetails->getRelativeNameWithoutSuffix());

        $controllerTemplatePath = __DIR__ . '/Resources/skeleton/crud/controller/Controller.tpl.php';


        $generator->generateController(
            $frontControllerClassDetails->getFullName(),
            $controllerTemplatePath,
            array_merge([
                'entity_full_class_name' => $entityClassDetails->getFullName(),
                'entity_class_name' => $entityClassDetails->getShortName(),
                'form_full_class_name' => $formClassDetails->getFullName(),
                'form_class_name' => $formClassDetails->getShortName(),
                'route_path' => Str::asRoutePath($frontControllerClassDetails->getRelativeNameWithoutSuffix()),
                'route_name' => $frontRouteName,
                'templates_path' => $frontTemplatesPath,
                'entity_var_plural' => $entityVarPlural,
                'entity_twig_var_plural' => $entityTwigVarPlural,
                'entity_var_singular' => $entityVarSingular,
                'entity_twig_var_singular' => $entityTwigVarSingular,
                'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
                'use_render_form' => method_exists(AbstractController::class, 'renderForm'),
            ],
                $repositoryVars
            )
        );
        $generator->generateController(
            $backControllerClassDetails->getFullName(),
            __DIR__ . '/Resources/skeleton/crud/controller/Controller.tpl.php',
            array_merge([
                'entity_full_class_name' => $entityClassDetails->getFullName(),
                'entity_class_name' => $entityClassDetails->getShortName(),
                'form_full_class_name' => $formClassDetails->getFullName(),
                'form_class_name' => $formClassDetails->getShortName(),
                'route_path' => Str::asRoutePath($backControllerClassDetails->getRelativeNameWithoutSuffix()),
                'route_name' => $backRouteName,
                'templates_path' => $backTemplatesPath,
                'entity_var_plural' => $entityVarPlural,
                'entity_twig_var_plural' => $entityTwigVarPlural,
                'entity_var_singular' => $entityVarSingular,
                'entity_twig_var_singular' => $entityTwigVarSingular,
                'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
                'use_render_form' => method_exists(AbstractController::class, 'renderForm'),
            ],
                $repositoryVars
            )
        );

        $this->formTypeRenderer->render(
            $formClassDetails,
            $entityDoctrineDetails->getFormFields(),
            $entityClassDetails
        );

        $frontTemplates = [
            '_delete_form' => [
                'route_name' => $frontRouteName,
                'entity_twig_var_singular' => $entityTwigVarSingular,
                'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
            ],
            '_form' => [],
            'edit' => [
                'entity_class_name' => $entityClassDetails->getShortName(),
                'entity_twig_var_singular' => $entityTwigVarSingular,
                'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
                'route_name' => $frontRouteName,
                'templates_path' => $frontTemplatesPath,
            ],
            'index' => [
                'entity_class_name' => $entityClassDetails->getShortName(),
                'entity_twig_var_plural' => $entityTwigVarPlural,
                'entity_twig_var_singular' => $entityTwigVarSingular,
                'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
                'entity_fields' => $entityDoctrineDetails->getDisplayFields(),
                'route_name' => $frontRouteName,
            ],
            'new' => [
                'entity_class_name' => $entityClassDetails->getShortName(),
                'route_name' => $frontRouteName,
                'templates_path' => $frontTemplatesPath,
            ],
            'show' => [
                'entity_class_name' => $entityClassDetails->getShortName(),
                'entity_twig_var_singular' => $entityTwigVarSingular,
                'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
                'entity_fields' => $entityDoctrineDetails->getDisplayFields(),
                'route_name' => $frontRouteName,
                'templates_path' => $frontTemplatesPath,
            ],
        ];

        $backTemplates = [
            '_delete_form' => [
                'route_name' => $backRouteName,
                'entity_twig_var_singular' => $entityTwigVarSingular,
                'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
            ],
            '_form' => [],
            'edit' => [
                'entity_class_name' => $entityClassDetails->getShortName(),
                'entity_twig_var_singular' => $entityTwigVarSingular,
                'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
                'route_name' => $backRouteName,
                'templates_path' => $backTemplatesPath,
            ],
            'index' => [
                'entity_class_name' => $entityClassDetails->getShortName(),
                'entity_twig_var_plural' => $entityTwigVarPlural,
                'entity_twig_var_singular' => $entityTwigVarSingular,
                'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
                'entity_fields' => $entityDoctrineDetails->getDisplayFields(),
                'route_name' => $backRouteName,
            ],
            'new' => [
                'entity_class_name' => $entityClassDetails->getShortName(),
                'route_name' => $backRouteName,
                'templates_path' => $backTemplatesPath,
            ],
            'show' => [
                'entity_class_name' => $entityClassDetails->getShortName(),
                'entity_twig_var_singular' => $entityTwigVarSingular,
                'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
                'entity_fields' => $entityDoctrineDetails->getDisplayFields(),
                'route_name' => $backRouteName,
                'templates_path' => $backTemplatesPath,
            ],
        ];

        foreach ($frontTemplates as $template => $variables) {
            $generator->generateTemplate(
                $frontTemplatesPath.'/'.$template.'.html.twig',
                __DIR__ . '/Resources/skeleton/crud/templates/'.$template.'.tpl.php',
                $variables
            );
        }

        foreach ($backTemplates as $template => $variables) {
            $generator->generateTemplate(
                $backTemplatesPath.'/'.$template.'.html.twig',
                __DIR__ . '/Resources/skeleton/crud/templates/'.$template.'.tpl.php',
                $variables
            );
        }

        $generator->writeChanges();

        $this->writeSuccessMessage($io);

        $io->text(sprintf('Next: Check your new CRUD by going to <fg=yellow>%s/</>', Str::asRoutePath($frontControllerClassDetails->getRelativeNameWithoutSuffix())));

    }


    private function pluralize(string $word): string
    {
        return $this->inflector->pluralize($word);

    }

    private function singularize(string $word): string
    {
        return $this->inflector->singularize($word);
    }
}