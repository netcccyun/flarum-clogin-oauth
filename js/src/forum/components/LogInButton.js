import app from "flarum/app";
import Button from 'flarum/components/Button';

/**
 * The `LogInButton` component displays a social login button which will open
 * a popup window containing the specified path.
 *
 * ### Attrs
 *
 * - `path`
 */
export default class LogInButton extends Button {
  static initAttrs(attrs) {
    attrs.className = (attrs.className || '') + ' LogInButton';

    if( /Android|SymbianOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Windows Phone|Midp/i.test(navigator.userAgent)) {
      attrs.onclick = function(){
        window.location.href = app.forum.attribute('baseUrl') + attrs.path;
      }
    }else{
      attrs.onclick = function(){
        const width = 580;
        const height = 400;
        const $window = $(window);
    
        window.open(
          app.forum.attribute('baseUrl') + attrs.path,
          'logInPopup',
          `width=${width},` +
            `height=${height},` +
            `top=${$window.height() / 2 - height / 2},` +
            `left=${$window.width() / 2 - width / 2},` +
            'status=no,scrollbars=yes,resizable=no'
        );
      }
    }

    super.initAttrs(attrs);
  }

}
