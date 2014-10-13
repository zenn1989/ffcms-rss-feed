<article class="article-item">
    <ol class="breadcrumb">
        <li><a href="{{ system.url }}">{{ language.global_main }}</a></li>
        <li><a href="{{ system.url }}/feed/">{{ language.feed_breadcrumb_main }}</a></li>
        <li><a href="{{ system.url }}/feed/category/{{ rssfeed.channel_id }}">{{ rssfeed.channel_title }}</a></li>
        <li class="active">{{ rssfeed.item_title|slice(0,30) }}{% if rssfeed.item_title|length > 30 %}...{% endif %}</li>
    </ol>
    <h1>{{ rssfeed.item_title }}</h1>
    <div class="meta">
        <span><i class="fa fa-list"></i><a href="{{ system.url }}/feed/category/{{ rssfeed.channel_id }}">{{ rssfeed.channel_title }}</a></span>
        <span><i class="fa fa-calendar"></i>{{ rssfeed.item_date }}</span>
        <span><i class="fa fa-arrow-right"></i><a href="{{ rssfeed.source_url }}" target="_blank">{{ rssfeed.source_url }}</a></span>
    </div>
    {% if rssfeed.item_image != null %}
    <img src="{{ system.script_url }}/{{ rssfeed.item_image }}" alt="{{ rssfeed.item_title }}" class="img-responsive" style="display: table;margin: 0 auto;" />
    {% endif %}
    {% if rssfeed.item_fulltext|length > 0 %}
    <p>{{ rssfeed.item_fulltext|replace({"\n":"</p><p>"}) }}</p> {# replace - change new lines to html paragraph tag - close & open #}
    {% else %}
    <p>{{ rssfeed.item_desc|replace({"\n":"</p><p>"}) }}</p>
    {% endif %}
</article>