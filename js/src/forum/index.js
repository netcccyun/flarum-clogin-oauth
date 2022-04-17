import { extend } from "flarum/extend";
import app from "flarum/app";

import SettingsPage from 'flarum/components/SettingsPage';
import Application from './components/Application';
import UnlinkModal from "./components/UnlinkModal";

import LogInButtons from "flarum/components/LogInButtons";
import LogInButton from 'flarum/components/LogInButton';
import Button from 'flarum/components/Button';

import config from '../config';

app.initializers.add(config.module.name, () => {
  extend(LogInButtons.prototype, "items", function (items) {

    if(app.forum.data.attributes['oauth_openqq'] == '1'){
      items.add("qq",
        <LogInButton className="Button LogInButton--QQ" icon="fab fa-qq" path="/api/oauth/login?type=qq">
          {app.translator.trans(`${config.module.name}.forum.with_qq_button`)}
        </LogInButton>
      );
    }

    if(app.forum.data.attributes['oauth_openwx'] == '1'){
      items.add("wx",
        <LogInButton className="Button LogInButton--WX" icon="fab fa-weixin" path="/api/oauth/login?type=wx">
          {app.translator.trans(`${config.module.name}.forum.with_wx_button`)}
        </LogInButton>
      );
    }

    if(app.forum.data.attributes['oauth_opensina'] == '1'){
      items.add("sina",
        <LogInButton className="Button LogInButton--WB" icon="fab fa-weibo" path="/api/oauth/login?type=sina">
          {app.translator.trans(`${config.module.name}.forum.with_sina_button`)}
        </LogInButton>
      );
    }

    return
        
  });

  extend(SettingsPage.prototype, 'accountItems', (items) => {

    if(app.forum.data.attributes['oauth_openqq'] == '1'){
      var is_qq_linked = app.session.user.data.attributes.is_qq_linked;
      if(is_qq_linked){
        items.add(`linkqq`,
          <Button className="Button Button--danger" icon="fab fa-qq"
          path="/api/oauth/link?type=qq" onclick={() => app.modal.show(UnlinkModal, {type: 'qq'})}>
              {app.translator.trans(`${config.module.name}.forum.modals.unlink_qq`)}
          </Button>
        );
      }else{
        items.add("linkqq",
          <LogInButton className="Button LogInButton--QQ" style="display: inline-block;" icon="fab fa-qq" path="/api/oauth/link?type=qq">
            {app.translator.trans(`${config.module.name}.forum.modals.link_qq`)}
          </LogInButton>
        );
      }
    }

    if(app.forum.data.attributes['oauth_openwx'] == '1'){
      var is_wx_linked = app.session.user.data.attributes.is_wx_linked;
      if(is_wx_linked){
        items.add(`linkwx`,
          <Button className="Button Button--danger" icon="fab fa-weixin"
          path="/api/oauth/link?type=wx" onclick={() => app.modal.show(UnlinkModal, {type: 'wx'})}>
              {app.translator.trans(`${config.module.name}.forum.modals.unlink_wx`)}
          </Button>
        );
      }else{
        items.add("linkwx",
          <LogInButton className="Button LogInButton--WX" style="display: inline-block;" icon="fab fa-weixin" path="/api/oauth/link?type=wx">
            {app.translator.trans(`${config.module.name}.forum.modals.link_wx`)}
          </LogInButton>
        );
      }
    }

    if(app.forum.data.attributes['oauth_opensina'] == '1'){
      var is_sina_linked = app.session.user.data.attributes.is_sina_linked;
      if(is_sina_linked){
        items.add(`linksina`,
          <Button className="Button Button--danger" icon="fab fa-weibo"
          path="/api/oauth/link?type=sina" onclick={() => app.modal.show(UnlinkModal, {type: 'sina'})}>
              {app.translator.trans(`${config.module.name}.forum.modals.unlink_sina`)}
          </Button>
        );
      }else{
        items.add("linksina",
          <LogInButton className="Button LogInButton--WB" style="display: inline-block;" icon="fab fa-weibo" path="/api/oauth/link?type=sina">
            {app.translator.trans(`${config.module.name}.forum.modals.link_sina`)}
          </LogInButton>
        );
      }
    }

});
});

app.oauth = new Application();