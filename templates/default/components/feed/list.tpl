<ol class="breadcrumb">
    <li><a href="{{ system.url }}">{{ language.global_main }}</a></li>
    <li><a href="{{ system.url }}/feed/">{{ language.feed_breadcrumb_main }}</a></li>
    <li class="active">{{ language.feed_breadcrumb_category }}</li>
</ol>
<h1>{{ language.feed_category_list }}</h1>
<hr />
<div class="row">
    <div class="col-md-12">
        <a href="{{ system.url }}/feed/" class="pull-right btn btn-default">{{ language.feed_category_allitem }}</a>
    </div>
</div>
{% for row in rsscat %}
    <h2><a href="{{ system.url }}/feed/category/{{ row.id }}">{{ row.title }}</a></h2>
    <p>{{ row.desc }}</p>
    <hr />
{% endfor %}