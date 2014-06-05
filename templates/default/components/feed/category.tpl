<ol class="breadcrumb">
    <li><a href="{{ system.url }}">{{ language.global_main }}</a></li>
    <li><a href="{{ system.url }}/feed/">{{ language.feed_breadcrumb_main }}</a></li>
    <li class="active">{{ rsscat.title }}</li>
</ol>
<h1>{{ language.feed_category_header }} - {{ rsscat.title }}</h1>
<p>{{ rsscat.desc }}</p>
<hr />
{% for item in rssfeed %}
    <h2><a href="{{ system.url }}/feed/item/{{ item.id }}.html">({{ item.date }}) {{ item.title }}</a></h2>
    <hr />
{% endfor %}
{{ pagination }}