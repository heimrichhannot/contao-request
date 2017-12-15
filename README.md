# Request

![](https://img.shields.io/packagist/v/heimrichhannot/contao-request.svg)
![](https://img.shields.io/packagist/l/heimrichhannot/contao-request.svg)
![](https://img.shields.io/packagist/dt/heimrichhannot/contao-request.svg)
[![](https://img.shields.io/travis/heimrichhannot/contao-request/master.svg)](https://travis-ci.org/heimrichhannot/contao-request/)
[![](https://img.shields.io/coveralls/heimrichhannot/contao-request/master.svg)](https://coveralls.io/github/heimrichhannot/contao-request)

Contao uses it own `Input` class, that check the request for $_GET, $_POST and more parameters.
This is done directly on $_GET, $_POST Server Parameters and for Tests it is not possible to simulate the HTTP-Server.
Here `HeimrichHannot\Request` put on and provide the sumilation of your own HTTP-Server object with help of `symfony/http-foundation`.

## Technical instruction

Use the following alternatives for contao `Input` or `Environment` calls

Contao | Request
---- | -----------
`\Input::get($strKey)` | `\HeimrichHannot\Request\Request::getGet($strKey)`
`\Input::post($strKey)` | `\HeimrichHannot\Request\Request::getPost($strKey)`
`\Input::postHtml($strKey)` | `\HeimrichHannot\Request\Request::getPostHtml($strKey)`
`\Input::postRaw($strKey)` | `\HeimrichHannot\Request\Request::getPostRaw($strKey)`
`\Input::setPost($strKey, $varValue)` | `\HeimrichHannot\Request\Request::setPost($strKey, $varValue)`
`\Input::setGet($strKey, $varValue)` | `\HeimrichHannot\Request\Request::setGet($strKey, $varValue)`
`isset($_GET[$strKey])` | `\HeimrichHannot\Request\Request::hasGet($strKey)`
`isset($_POST[$strKey])` | `\HeimrichHannot\Request\Request::hasPost($strKey)`
`\Environment::get('isAjaxRequest')` | `\HeimrichHannot\Request\Request::getInstance()->isXmlHttpRequest()`


