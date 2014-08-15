{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_components_feed_delete_title }}</small></h1>
<hr />
{% include 'components/feed/menu_include.tpl' %}
<p>{{ language.admin_components_feed_delete_desc }}</p>
<blockquote>
    {{ rssfeed.title }} <small>{{ rssfeed.url }}</small>
</blockquote>
<form action="" method="post">
    <input type="submit" name="rss_submit" class="btn btn-danger" value="{{ language.admin_components_feed_delete_button }}" />
</form>