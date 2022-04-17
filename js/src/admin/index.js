import app from "flarum/app";

import config from '../config';

app.initializers.add(config.module.name, (app) => {

  app.extensionData
    .for(config.module.name)
    .registerSetting(
      {
        setting: `${config.module.name}.appurl`,
        label: app.translator.trans(
          `${config.module.name}.admin.appurl_label`
        ),
        help: app.translator.trans(
          `${config.module.name}.admin.appurl_help`
        ),
        type: "text",
      },
      30
    )
    .registerSetting(
      {
        setting: `${config.module.name}.appid`,
        label: app.translator.trans(
          `${config.module.name}.admin.appid_label`
        ),
        type: "text",
      },
      30
    )
    .registerSetting(
      {
        setting: `${config.module.name}.appkey`,
        label: app.translator.trans(
          `${config.module.name}.admin.appkey_label`
        ),
        type: "text",
      },
      30
    )
    .registerSetting(
      {
        setting: `${config.module.name}.openqq`,
        label: app.translator.trans(
          `${config.module.name}.admin.openqq_label`
        ),
        type: "boolean",
      },
      30
    )
    .registerSetting(
      {
        setting: `${config.module.name}.openwx`,
        label: app.translator.trans(
          `${config.module.name}.admin.openwx_label`
        ),
        type: "boolean",
      },
      30
    )
    .registerSetting(
      {
        setting: `${config.module.name}.opensina`,
        label: app.translator.trans(
          `${config.module.name}.admin.opensina_label`
        ),
        type: "boolean",
      },
      30
    );
  return;
});
