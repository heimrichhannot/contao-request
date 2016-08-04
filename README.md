# Request

Contao uses it own `Input` class, that check the request for $_GET, $_POST and more parameters.
This is done directly on $_GET, $_POST Server Parameters and for Tests it is not possible to simulate the HTTP-Server.
Here `HeimrichHannot\Request` put on and provide the sumilation of your own HTTP-Server object with help of `symfony/http-foundation`.

## Technical instruction

Use the following alternatives for contao `Input` or `Environment` calls

Contao | Request
---- | -----------
`\Input::get($strKey)` | `\HeimrichHannot\Request\Request::getGet($strKey)`
`\Input::post($strKey)` | `\HeimrichHannot\Request\Request::getPost($strKey)`
`\Environment::get('isAjaxRequest')` | `\HeimrichHannot\Request\Request::getInstance()->isXmlHttpRequest()`


