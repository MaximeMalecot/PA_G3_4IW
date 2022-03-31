<?= $base ?>

{% block title%}New <?= $entity_class_name ?>{% endblock %}

{% block body %}
<style>
    .example-wrapper { margin: 1em auto; max-width: 800px; width: 95%; font: 18px/1.5 sans-serif; }
    .example-wrapper code { background: #F5F5F5; padding: 2px 6px; }
</style>

<div class="example-wrapper">
    <h1>Create new <?= $entity_class_name ?></h1>

    {{ include('<?= $templates_path ?>/_form.html.twig') }}

    <a href="{{ path('<?= $route_name ?>_index') }}">back to list</a>
</div>
{% endblock %}
