{% import 'macro/settings.tpl' as settingstpl %}
{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_component_news_settings }}</small></h1>
<hr />
{% include 'components/feed/menu_include.tpl' %}
{% if notify.save_success %}
    {{ notifytpl.success(language.admin_extension_config_update_success) }}
{% endif %}
<form action="" method="post" class="form-horizontal" role="form">
    <fieldset>
        {{ settingstpl.textgroup('item_per_page', config.item_per_page, language.admin_components_feed_config_count_title, language.admin_components_feed_config_count_desc) }}
        <input type="submit" name="submit" value="{{ language.admin_extension_save_button }}" class="btn btn-success"/>
    </fieldset>
</form>