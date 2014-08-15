<h1>{{ extension.title }}<small>{{ language.admin_components_feed_list_title }}</small></h1>
<hr />
{% include 'components/feed/menu_include.tpl' %}
<p class="pull-right">
    <a href="?object=components&action=feed&make=add" class="btn btn-success">{{ language.admin_components_feed_button_add }}</a>
</p>
<table class="table table-responsive table-bordered">
    <thead>
    <tr>
        <th>№</th>
        <th>{{ language.admin_components_feed_th_title }}</th>
        <th>{{ language.admin_components_feed_th_source }}</th>
        <th>{{ language.admin_components_feed_th_actions }}</th>
    </tr>
    </thead>
    <tbody>
    {% for row in rssfeed %}
    <tr>
        <td>{{ row.id }}</td>
        <td>{{ row.title }}</td>
        <td>{{ row.url }}</td>
        <td class="text-center"> <a href="?object=components&action=feed&make=edit&id={{ row.id }}" title="Edit"><i class="fa fa-pencil-square-o fa-lg"></i></a>
            <a href="?object=components&action=feed&make=delete&id={{ row.id }}" title="Delete"><i class="fa fa-trash-o fa-lg"></i></a></td>
    </tr>
    {% endfor %}
    </tbody>
</table>