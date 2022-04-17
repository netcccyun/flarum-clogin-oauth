import Modal from 'flarum/components/Modal';
import Button from 'flarum/components/Button';

import config from '../../config';

export default class UnlinkModal extends Modal {
    className() {
        return `UnlinkModal Modal--small`;
    }

    title() {
        var type = this.attrs.type;
        return app.translator.trans(`${config.module.name}.forum.modals.unlink_${type}`);
    }

    content() {

        var type = this.attrs.type;

        return (
            <div className="Modal-body">
                <div className="Form Form--centered">
                    <div className="Form-group" id="submit-button-group">
                        <h3>{app.translator.trans(`${config.module.name}.forum.modals.confirm_${type}`)}</h3>
                        {(app.session.user.data.attributes.providersCount <= 1)
                            ?
                            <p style="color: #d83e3e"><i className="fas fa-exclamation-triangle fa-fw" />
                                <b>{app.translator.trans(`${config.module.name}.forum.modals.no_providers`)}</b>
                            </p>
                            : ''
                        }
                        <br />
                        <div className="ButtonGroup">
                            <Button type={'submit'} className={`Button Button--danger`} icon={'fas fa-exclamation-triangle'}
                                loading={this.loading}>
                                {app.translator.trans(`${config.module.name}.forum.modals.buttons.confirm`)}
                            </Button>
                            <Button className={'Button Button--primary'} icon={'fas fa-exclamation-triangle'}
                                onclick={() => this.hide()} disabled={this.loading}>
                                {app.translator.trans(`${config.module.name}.forum.modals.buttons.cancel`)}
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    onsubmit(e) {

        let alert;
        var type = this.attrs.type;

        e.preventDefault();
        this.loading = true;

        app.request({
            method: 'POST',
            url: `${app.forum.attribute('apiUrl')}/oauth/unlink?type=${type}`,
        }).then(() => {
            app.session.user.savePreferences();
            this.hide();
            alert = app.alerts.show({ type: 'success' }, app.translator.trans(`${config.module.name}.forum.alerts.unlink_success`));
        });

        setTimeout(() => {
            app.alerts.dismiss(alert);
        }, 5000);
    }
}
