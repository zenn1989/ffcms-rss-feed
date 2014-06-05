<ol class="breadcrumb">
    <li><a href="{{ system.url }}">{{ language.global_main }}</a></li>
    <li class="active">{{ language.feed_breadcrumb_main }}</li>
</ol>
<h1>{{ language.feed_global_title }}</h1>
<hr />
<div class="row">
    <div class="col-md-12">
        <a href="{{ system.url }}/feed/category" class="pull-right btn btn-default">{{ language.feed_category_title }}</a>
    </div>
</div>
{% for item in rssfeed %}
    <h2><a href="{{ system.url }}/feed/category/{{ item.cat_id }}">{{ item.cat_title }} ({{ item.date }})</a>: <a href="{{ system.url }}/feed/item/{{ item.id }}.html">{{ item.title }}</a></h2>
    <hr />
{% endfor %}
{{ pagination }}