<script src="{{ system.theme }}/js/maxlength.js"></script>
<script type="text/javascript">
    $(document).ready(
            function()
            {
                $('input[maxlength]').maxlength({alwaysShow: true});
                $('textarea[maxlength]').maxlength({alwaysShow: true});
            }
    );
</script>
{% import 'macro/notify.tpl' as notifytpl %}
<h1>{{ extension.title }}<small>{{ language.admin_components_feed_edit_title }}</small></h1>
<hr />
{% include 'components/feed/menu_include.tpl' %}
{% if notify.incorrent_title %}
    {{ notifytpl.error(language.admin_components_feed_notify_length) }}
{% endif %}
{% if notify.incorrent_url %}
    {{ notifytpl.error(language.admin_components_feed_notify_source_wrong) }}
{% endif %}
<form action="" method="post" class="form-horizontal">
    <div class="tabbable" id="contentTab">
        <ul class="nav nav-tabs">
            {% for itemlang in system.languages %}
                <li{% if itemlang == system.lang %} class="active"{% endif %}><a href="#{{ itemlang }}" data-toggle="tab">{{ language.language }}: {{ itemlang|upper }}</a></li>
            {% endfor %}
        </ul>
        <div class="tab-content">
            <br />
            {% for itemlang in system.languages %}
            <div class="tab-pane fade{% if itemlang == system.lang %} in active{% endif %}" id="{{ itemlang }}">
                <div class="form-group">
                    <label class="control-label col-lg-3">{{ language.admin_components_feed_edit_form_title }}[{{ itemlang }}]</label>

                    <div class="col-lg-9">
                        <input type="text" class="form-control" name="rss_title[{{ itemlang }}]" value="{{ rssfeed.title[itemlang] }}" maxlength="150">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-lg-3">{{ language.admin_components_feed_edit_form_desc }}[{{ itemlang }}]</label>

                    <div class="col-lg-9">
                        <textarea name="rss_desc[{{ itemlang }}]" class="form-control" maxlength="500">{{ rssfeed.desc[itemlang] }}</textarea>
                        <span class="help-block">{{ language.admin_components_feed_edit_form_desc_helper }}</span>
                    </div>
                </div>
            </div>
            {% endfor %}
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3">{{ language.admin_components_feed_edit_form_url }}</label>

        <div class="col-lg-9">
            <input type="text" class="form-control" name="rss_url" value="{{ rssfeed.url }}">
            <span class="help-block">{{ language.admin_components_feed_edit_form_url_helper }}</span>
        </div>
    </div>
    <input type="submit" name="rss_submit" class="btn btn-success" value="{{ language.admin_components_feed_edit_button_save }}" />
</form>