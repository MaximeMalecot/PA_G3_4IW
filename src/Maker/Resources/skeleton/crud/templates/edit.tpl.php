<?= $helper->getHeadPrintCode('Edit '.$entity_class_name) ?>

{% block body %}
<style>
    .example-wrapper { margin: 1em auto; max-width: 800px; width: 95%; font: 18px/1.5 sans-serif; }
    .example-wrapper code { background: #F5F5F5; padding: 2px 6px; }
</style>

<div class="example-wrapper">
    <h1>Edit <?= $entity_class_name ?></h1>

    {{ include('<?= $templates_path ?>/_form.html.twig', {'button_label': 'Update'}) }}

    <a href="{{ path('<?= $route_name ?>_index') }}">back to list</a>

    {{ include('<?= $templates_path ?>/_delete_form.html.twig') }}
    </div>
{% endblock %}
