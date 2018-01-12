/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 22);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

// https://github.com/zloirock/core-js/issues/86#issuecomment-115759028
var global = module.exports = typeof window != 'undefined' && window.Math == Math
  ? window : typeof self != 'undefined' && self.Math == Math ? self
  // eslint-disable-next-line no-new-func
  : Function('return this')();
if (typeof __g == 'number') __g = global; // eslint-disable-line no-undef


/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

var store = __webpack_require__(25)('wks');
var uid = __webpack_require__(12);
var Symbol = __webpack_require__(0).Symbol;
var USE_SYMBOL = typeof Symbol == 'function';

var $exports = module.exports = function (name) {
  return store[name] || (store[name] =
    USE_SYMBOL && Symbol[name] || (USE_SYMBOL ? Symbol : uid)('Symbol.' + name));
};

$exports.store = store;


/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

var isObject = __webpack_require__(3);
module.exports = function (it) {
  if (!isObject(it)) throw TypeError(it + ' is not an object!');
  return it;
};


/***/ }),
/* 3 */
/***/ (function(module, exports) {

module.exports = function (it) {
  return typeof it === 'object' ? it !== null : typeof it === 'function';
};


/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

// optional / simple context binding
var aFunction = __webpack_require__(5);
module.exports = function (fn, that, length) {
  aFunction(fn);
  if (that === undefined) return fn;
  switch (length) {
    case 1: return function (a) {
      return fn.call(that, a);
    };
    case 2: return function (a, b) {
      return fn.call(that, a, b);
    };
    case 3: return function (a, b, c) {
      return fn.call(that, a, b, c);
    };
  }
  return function (/* ...args */) {
    return fn.apply(that, arguments);
  };
};


/***/ }),
/* 5 */
/***/ (function(module, exports) {

module.exports = function (it) {
  if (typeof it != 'function') throw TypeError(it + ' is not a function!');
  return it;
};


/***/ }),
/* 6 */
/***/ (function(module, exports) {

var core = module.exports = { version: '2.5.3' };
if (typeof __e == 'number') __e = core; // eslint-disable-line no-undef


/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

// Thank's IE8 for his funny defineProperty
module.exports = !__webpack_require__(14)(function () {
  return Object.defineProperty({}, 'a', { get: function () { return 7; } }).a != 7;
});


/***/ }),
/* 8 */
/***/ (function(module, exports) {

var toString = {}.toString;

module.exports = function (it) {
  return toString.call(it).slice(8, -1);
};


/***/ }),
/* 9 */
/***/ (function(module, exports, __webpack_require__) {

var anObject = __webpack_require__(2);
var IE8_DOM_DEFINE = __webpack_require__(27);
var toPrimitive = __webpack_require__(28);
var dP = Object.defineProperty;

exports.f = __webpack_require__(7) ? Object.defineProperty : function defineProperty(O, P, Attributes) {
  anObject(O);
  P = toPrimitive(P, true);
  anObject(Attributes);
  if (IE8_DOM_DEFINE) try {
    return dP(O, P, Attributes);
  } catch (e) { /* empty */ }
  if ('get' in Attributes || 'set' in Attributes) throw TypeError('Accessors not supported!');
  if ('value' in Attributes) O[P] = Attributes.value;
  return O;
};


/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var __extends = this && this.__extends || function () {
    var extendStatics = Object.setPrototypeOf || { __proto__: [] } instanceof Array && function (d, b) {
        d.__proto__ = b;
    } || function (d, b) {
        for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    };
    return function (d, b) {
        extendStatics(d, b);
        function __() {
            this.constructor = d;
        }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
}();
Object.defineProperty(exports, "__esModule", { value: true });
function findPosition(element, attribute) {
    var pos = 0;
    if (element.parentNode) {
        for (var _i = 0, _a = element.parentNode.childNodes; _i < _a.length; _i++) {
            var sibling = _a[_i];
            if (sibling instanceof Element) {
                if (element === sibling) {
                    break;
                }
                if (sibling.hasAttribute(attribute)) {
                    pos++;
                }
            }
        }
    }
    return pos;
}
function toggleClass(element, cssClass) {
    if (element.classList.contains(cssClass)) {
        element.classList.remove(cssClass);
    } else {
        element.classList.add(cssClass);
    }
}
function findItemPosition(element) {
    return findPosition(element, "data-item-id");
}
function findContainerPosition(element) {
    return findPosition(element, "data-id");
}
function getContainerCount(element) {
    var count = 0;
    for (var _i = 0, _a = element.childNodes; _i < _a.length; _i++) {
        var child = _a[_i];
        if (child instanceof Element) {
            if (child.hasAttribute("data-id") || child.hasAttribute("data-item-type")) {
                count++;
            }
        }
    }
    return count;
}
exports.getContainerCount = getContainerCount;
var ContainerType;
(function (ContainerType) {
    ContainerType["Column"] = "vbox";
    ContainerType["Horizontal"] = "hbox";
    ContainerType["Layout"] = "Layout";
})(ContainerType = exports.ContainerType || (exports.ContainerType = {}));
function getContainer(element) {
    if (element.hasAttribute("data-token") || element.hasAttribute("data-layout-id") || element.hasAttribute("data-id")) {
        return new Container(element.getAttribute("data-id"), element.getAttribute("data-container"), element, element.getAttribute("data-token"), element.getAttribute("data-layout-id") || element.getAttribute("data-id"));
    }
    throw "element is not a container, or is not initialized properly";
}
exports.getContainer = getContainer;
function getItem(element) {
    if (element.hasAttribute("data-item-id")) {
        return new Item(element.getAttribute("data-id") || element.getAttribute("data-item-id"), element.getAttribute("data-item-type") || "null", !element.hasAttribute("data-id"), element);
    }
    throw "element is not an item";
}
exports.getItem = getItem;
var Item = function () {
    function Item(id, type, readonly, element) {
        this.id = id;
        this.type = type;
        this.readonly = readonly;
        this.element = element;
    }
    Item.prototype.getPosition = function () {
        return findItemPosition(this.element);
    };
    Item.prototype.toggleCollapse = function () {
        toggleClass(this.element, "collapsed");
    };
    Item.prototype.findParentElement = function () {
        var current = this.element;
        while (current.parentElement) {
            current = current.parentElement;
            if (current.hasAttribute("data-id")) {
                return current;
            }
        }
        throw "Parent has not identifier";
    };
    Item.prototype.getParentContainer = function () {
        return getContainer(this.findParentElement());
    };
    Item.prototype.findParentId = function () {
        return this.findParentElement().getAttribute("data-id") || "";
    };
    Item.DefaultStyle = "_default";
    return Item;
}();
exports.Item = Item;
var Container = function (_super) {
    __extends(Container, _super);
    function Container(id, type, element, token, layoutId) {
        var _this = _super.call(this, id, type, type === ContainerType.Horizontal, element) || this;
        _this.layoutId = layoutId;
        _this.token = token;
        return _this;
    }
    Container.prototype.getPosition = function () {
        return findContainerPosition(this.element);
    };
    return Container;
}(Item);
exports.Container = Container;

/***/ }),
/* 11 */
/***/ (function(module, exports, __webpack_require__) {

// getting tag from 19.1.3.6 Object.prototype.toString()
var cof = __webpack_require__(8);
var TAG = __webpack_require__(1)('toStringTag');
// ES3 wrong here
var ARG = cof(function () { return arguments; }()) == 'Arguments';

// fallback for IE11 Script Access Denied error
var tryGet = function (it, key) {
  try {
    return it[key];
  } catch (e) { /* empty */ }
};

module.exports = function (it) {
  var O, T, B;
  return it === undefined ? 'Undefined' : it === null ? 'Null'
    // @@toStringTag case
    : typeof (T = tryGet(O = Object(it), TAG)) == 'string' ? T
    // builtinTag case
    : ARG ? cof(O)
    // ES3 arguments fallback
    : (B = cof(O)) == 'Object' && typeof O.callee == 'function' ? 'Arguments' : B;
};


/***/ }),
/* 12 */
/***/ (function(module, exports) {

var id = 0;
var px = Math.random();
module.exports = function (key) {
  return 'Symbol('.concat(key === undefined ? '' : key, ')_', (++id + px).toString(36));
};


/***/ }),
/* 13 */
/***/ (function(module, exports, __webpack_require__) {

var dP = __webpack_require__(9);
var createDesc = __webpack_require__(29);
module.exports = __webpack_require__(7) ? function (object, key, value) {
  return dP.f(object, key, createDesc(1, value));
} : function (object, key, value) {
  object[key] = value;
  return object;
};


/***/ }),
/* 14 */
/***/ (function(module, exports) {

module.exports = function (exec) {
  try {
    return !!exec();
  } catch (e) {
    return true;
  }
};


/***/ }),
/* 15 */
/***/ (function(module, exports, __webpack_require__) {

var isObject = __webpack_require__(3);
var document = __webpack_require__(0).document;
// typeof document.createElement is 'object' in old IE
var is = isObject(document) && isObject(document.createElement);
module.exports = function (it) {
  return is ? document.createElement(it) : {};
};


/***/ }),
/* 16 */
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__(0);
var hide = __webpack_require__(13);
var has = __webpack_require__(17);
var SRC = __webpack_require__(12)('src');
var TO_STRING = 'toString';
var $toString = Function[TO_STRING];
var TPL = ('' + $toString).split(TO_STRING);

__webpack_require__(6).inspectSource = function (it) {
  return $toString.call(it);
};

(module.exports = function (O, key, val, safe) {
  var isFunction = typeof val == 'function';
  if (isFunction) has(val, 'name') || hide(val, 'name', key);
  if (O[key] === val) return;
  if (isFunction) has(val, SRC) || hide(val, SRC, O[key] ? '' + O[key] : TPL.join(String(key)));
  if (O === global) {
    O[key] = val;
  } else if (!safe) {
    delete O[key];
    hide(O, key, val);
  } else if (O[key]) {
    O[key] = val;
  } else {
    hide(O, key, val);
  }
// add fake Function#toString for correct work wrapped methods / constructors with methods like LoDash isNative
})(Function.prototype, TO_STRING, function toString() {
  return typeof this == 'function' && this[SRC] || $toString.call(this);
});


/***/ }),
/* 17 */
/***/ (function(module, exports) {

var hasOwnProperty = {}.hasOwnProperty;
module.exports = function (it, key) {
  return hasOwnProperty.call(it, key);
};


/***/ }),
/* 18 */
/***/ (function(module, exports) {

module.exports = {};


/***/ }),
/* 19 */
/***/ (function(module, exports, __webpack_require__) {

var ctx = __webpack_require__(4);
var invoke = __webpack_require__(38);
var html = __webpack_require__(39);
var cel = __webpack_require__(15);
var global = __webpack_require__(0);
var process = global.process;
var setTask = global.setImmediate;
var clearTask = global.clearImmediate;
var MessageChannel = global.MessageChannel;
var Dispatch = global.Dispatch;
var counter = 0;
var queue = {};
var ONREADYSTATECHANGE = 'onreadystatechange';
var defer, channel, port;
var run = function () {
  var id = +this;
  // eslint-disable-next-line no-prototype-builtins
  if (queue.hasOwnProperty(id)) {
    var fn = queue[id];
    delete queue[id];
    fn();
  }
};
var listener = function (event) {
  run.call(event.data);
};
// Node.js 0.9+ & IE10+ has setImmediate, otherwise:
if (!setTask || !clearTask) {
  setTask = function setImmediate(fn) {
    var args = [];
    var i = 1;
    while (arguments.length > i) args.push(arguments[i++]);
    queue[++counter] = function () {
      // eslint-disable-next-line no-new-func
      invoke(typeof fn == 'function' ? fn : Function(fn), args);
    };
    defer(counter);
    return counter;
  };
  clearTask = function clearImmediate(id) {
    delete queue[id];
  };
  // Node.js 0.8-
  if (__webpack_require__(8)(process) == 'process') {
    defer = function (id) {
      process.nextTick(ctx(run, id, 1));
    };
  // Sphere (JS game engine) Dispatch API
  } else if (Dispatch && Dispatch.now) {
    defer = function (id) {
      Dispatch.now(ctx(run, id, 1));
    };
  // Browsers with MessageChannel, includes WebWorkers
  } else if (MessageChannel) {
    channel = new MessageChannel();
    port = channel.port2;
    channel.port1.onmessage = listener;
    defer = ctx(port.postMessage, port, 1);
  // Browsers with postMessage, skip WebWorkers
  // IE8 has postMessage, but it's sync & typeof its postMessage is 'object'
  } else if (global.addEventListener && typeof postMessage == 'function' && !global.importScripts) {
    defer = function (id) {
      global.postMessage(id + '', '*');
    };
    global.addEventListener('message', listener, false);
  // IE8-
  } else if (ONREADYSTATECHANGE in cel('script')) {
    defer = function (id) {
      html.appendChild(cel('script'))[ONREADYSTATECHANGE] = function () {
        html.removeChild(this);
        run.call(id);
      };
    };
  // Rest old browsers
  } else {
    defer = function (id) {
      setTimeout(ctx(run, id, 1), 0);
    };
  }
}
module.exports = {
  set: setTask,
  clear: clearTask
};


/***/ }),
/* 20 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

// 25.4.1.5 NewPromiseCapability(C)
var aFunction = __webpack_require__(5);

function PromiseCapability(C) {
  var resolve, reject;
  this.promise = new C(function ($$resolve, $$reject) {
    if (resolve !== undefined || reject !== undefined) throw TypeError('Bad Promise constructor');
    resolve = $$resolve;
    reject = $$reject;
  });
  this.resolve = aFunction(resolve);
  this.reject = aFunction(reject);
}

module.exports.f = function (C) {
  return new PromiseCapability(C);
};


/***/ }),
/* 21 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", { value: true });
var item_1 = __webpack_require__(10);
var menu_1 = __webpack_require__(52);
var State = function () {
    function State(handler) {
        var _this = this;
        this.containers = [];
        this.handler = handler;
        this.drake = dragula({
            copy: function (element, source) {
                try {
                    if (item_1.getContainer(source).readonly) {
                        return true;
                    }
                } catch (error) {
                    return true;
                }
                try {
                    return item_1.getItem(element).readonly;
                } catch (error) {
                    return false;
                }
            },
            accepts: function (element, target) {
                try {
                    return !item_1.getContainer(target).readonly;
                } catch (error) {
                    return false;
                }
            },
            invalid: function (element) {
                var current = element;
                while (current) {
                    if (current.hasAttribute("data-menu")) {
                        return true;
                    }
                    if (current.hasAttribute("data-item-id")) {
                        return false;
                    }
                    if (!current.parentElement) {
                        break;
                    }
                    current = current.parentElement;
                }
                return true;
            },
            revertOnSpill: true,
            removeOnSpill: false,
            direction: 'vertical'
        });
        this.drake.on('drop', function (element, target, source, sibling) {
            _this.onDrop(element, target, source, sibling);
        });
        this.drake.on('over', function (element, source) {
            _this.onOver(element, source);
        });
    }
    State.prototype.translate = function (text, variables) {
        for (var name_1 in variables) {
            text = text.replace(name_1, variables[name_1]);
        }
        return text;
    };
    State.prototype.remove = function (element) {
        var index = this.drake.containers.indexOf(element);
        if (-1 !== index) {
            this.drake.containers.splice(index);
        }
        if (element.parentElement) {
            element.parentElement.removeChild(element);
        }
    };
    State.prototype.cancel = function (error, element) {
        if (error) {
            this.handler.debug(error);
        }
        if (element) {
            element.remove();
        }
        this.drake.cancel(true);
    };
    State.prototype.onOver = function (element, source) {
        if (element instanceof HTMLElement) {
            element.style.cssFloat = 'none';
        }
    };
    State.prototype.onDrop = function (element, target, source, sibling) {
        var _this = this;
        try {
            var container_1 = item_1.getContainer(target);
            var item = item_1.getItem(element);
            if (container_1.readonly) {
                throw "container is readonly";
            }
            if (item.readonly) {
                this.handler.addItem(container_1.token, container_1.layoutId, container_1.id, item.type, item.id, item.getPosition()).then(function (item) {
                    element.parentElement.replaceChild(item, element);
                    _this.initItem(item, container_1);
                    _this.init(item);
                }).catch(function (error) {
                    _this.cancel(error, element);
                });
            } else {
                this.handler.moveItem(container_1.token, container_1.layoutId, container_1.id, item.id, item.getPosition()).catch(function (error) {
                    _this.cancel(error, element);
                });
            }
        } catch (error) {
            this.cancel(error);
        }
    };
    State.prototype.init = function (context) {
        this.collectLayouts(context);
        this.collectSources(context);
    };
    State.prototype.initContainer = function (element, parent) {
        if (element.hasAttribute("droppable")) {
            return;
        }
        if (!element.hasAttribute("data-container") || !element.hasAttribute("data-id")) {
            return;
        }
        element.setAttribute("data-token", parent.token);
        element.setAttribute("data-layout-id", parent.layoutId);
        var container = item_1.getContainer(element);
        if (container.type !== item_1.ContainerType.Horizontal) {
            element.setAttribute("droppable", "1");
            this.drake.containers.push(container.element);
        }
        this.collectItems(container);
        menu_1.createMenu(this, container);
    };
    State.prototype.initItem = function (element, parent) {
        if (element.hasAttribute("draggable")) {
            return;
        }
        if (!element.hasAttribute("data-item-id")) {
            return;
        }
        var item = item_1.getItem(element);
        element.setAttribute("draggable", "1");
        if (!item.readonly) {
            menu_1.createMenu(this, item);
        }
    };
    State.prototype.collectSources = function (context) {
        for (var _i = 0, _a = context.querySelectorAll("[data-layout-source]"); _i < _a.length; _i++) {
            var source = _a[_i];
            this.drake.containers.push(source);
        }
    };
    State.prototype.collectItems = function (container) {
        for (var _i = 0, _a = container.element.childNodes; _i < _a.length; _i++) {
            var element = _a[_i];
            if (element instanceof Element) {
                if (element.hasAttribute("data-item-id")) {
                    this.initItem(element, container);
                } else {
                    this.initContainer(element, container);
                }
            }
        }
    };
    State.prototype.collectLayouts = function (context) {
        for (var _i = 0, _a = context.querySelectorAll("[data-layout]"); _i < _a.length; _i++) {
            var element = _a[_i];
            if (!element.hasAttribute("data-token") || !element.hasAttribute("data-id")) {
                continue;
            }
            var layout = item_1.getContainer(element);
            element.setAttribute("droppable", "1");
            this.drake.containers.push(layout.element);
            this.collectItems(layout);
            menu_1.createMenu(this, layout);
        }
    };
    return State;
}();
exports.State = State;

/***/ }),
/* 22 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(23);
module.exports = __webpack_require__(47);


/***/ }),
/* 23 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var LIBRARY = __webpack_require__(24);
var global = __webpack_require__(0);
var ctx = __webpack_require__(4);
var classof = __webpack_require__(11);
var $export = __webpack_require__(26);
var isObject = __webpack_require__(3);
var aFunction = __webpack_require__(5);
var anInstance = __webpack_require__(30);
var forOf = __webpack_require__(31);
var speciesConstructor = __webpack_require__(37);
var task = __webpack_require__(19).set;
var microtask = __webpack_require__(40)();
var newPromiseCapabilityModule = __webpack_require__(20);
var perform = __webpack_require__(41);
var promiseResolve = __webpack_require__(42);
var PROMISE = 'Promise';
var TypeError = global.TypeError;
var process = global.process;
var $Promise = global[PROMISE];
var isNode = classof(process) == 'process';
var empty = function () { /* empty */ };
var Internal, newGenericPromiseCapability, OwnPromiseCapability, Wrapper;
var newPromiseCapability = newGenericPromiseCapability = newPromiseCapabilityModule.f;

var USE_NATIVE = !!function () {
  try {
    // correct subclassing with @@species support
    var promise = $Promise.resolve(1);
    var FakePromise = (promise.constructor = {})[__webpack_require__(1)('species')] = function (exec) {
      exec(empty, empty);
    };
    // unhandled rejections tracking support, NodeJS Promise without it fails @@species test
    return (isNode || typeof PromiseRejectionEvent == 'function') && promise.then(empty) instanceof FakePromise;
  } catch (e) { /* empty */ }
}();

// helpers
var isThenable = function (it) {
  var then;
  return isObject(it) && typeof (then = it.then) == 'function' ? then : false;
};
var notify = function (promise, isReject) {
  if (promise._n) return;
  promise._n = true;
  var chain = promise._c;
  microtask(function () {
    var value = promise._v;
    var ok = promise._s == 1;
    var i = 0;
    var run = function (reaction) {
      var handler = ok ? reaction.ok : reaction.fail;
      var resolve = reaction.resolve;
      var reject = reaction.reject;
      var domain = reaction.domain;
      var result, then;
      try {
        if (handler) {
          if (!ok) {
            if (promise._h == 2) onHandleUnhandled(promise);
            promise._h = 1;
          }
          if (handler === true) result = value;
          else {
            if (domain) domain.enter();
            result = handler(value);
            if (domain) domain.exit();
          }
          if (result === reaction.promise) {
            reject(TypeError('Promise-chain cycle'));
          } else if (then = isThenable(result)) {
            then.call(result, resolve, reject);
          } else resolve(result);
        } else reject(value);
      } catch (e) {
        reject(e);
      }
    };
    while (chain.length > i) run(chain[i++]); // variable length - can't use forEach
    promise._c = [];
    promise._n = false;
    if (isReject && !promise._h) onUnhandled(promise);
  });
};
var onUnhandled = function (promise) {
  task.call(global, function () {
    var value = promise._v;
    var unhandled = isUnhandled(promise);
    var result, handler, console;
    if (unhandled) {
      result = perform(function () {
        if (isNode) {
          process.emit('unhandledRejection', value, promise);
        } else if (handler = global.onunhandledrejection) {
          handler({ promise: promise, reason: value });
        } else if ((console = global.console) && console.error) {
          console.error('Unhandled promise rejection', value);
        }
      });
      // Browsers should not trigger `rejectionHandled` event if it was handled here, NodeJS - should
      promise._h = isNode || isUnhandled(promise) ? 2 : 1;
    } promise._a = undefined;
    if (unhandled && result.e) throw result.v;
  });
};
var isUnhandled = function (promise) {
  return promise._h !== 1 && (promise._a || promise._c).length === 0;
};
var onHandleUnhandled = function (promise) {
  task.call(global, function () {
    var handler;
    if (isNode) {
      process.emit('rejectionHandled', promise);
    } else if (handler = global.onrejectionhandled) {
      handler({ promise: promise, reason: promise._v });
    }
  });
};
var $reject = function (value) {
  var promise = this;
  if (promise._d) return;
  promise._d = true;
  promise = promise._w || promise; // unwrap
  promise._v = value;
  promise._s = 2;
  if (!promise._a) promise._a = promise._c.slice();
  notify(promise, true);
};
var $resolve = function (value) {
  var promise = this;
  var then;
  if (promise._d) return;
  promise._d = true;
  promise = promise._w || promise; // unwrap
  try {
    if (promise === value) throw TypeError("Promise can't be resolved itself");
    if (then = isThenable(value)) {
      microtask(function () {
        var wrapper = { _w: promise, _d: false }; // wrap
        try {
          then.call(value, ctx($resolve, wrapper, 1), ctx($reject, wrapper, 1));
        } catch (e) {
          $reject.call(wrapper, e);
        }
      });
    } else {
      promise._v = value;
      promise._s = 1;
      notify(promise, false);
    }
  } catch (e) {
    $reject.call({ _w: promise, _d: false }, e); // wrap
  }
};

// constructor polyfill
if (!USE_NATIVE) {
  // 25.4.3.1 Promise(executor)
  $Promise = function Promise(executor) {
    anInstance(this, $Promise, PROMISE, '_h');
    aFunction(executor);
    Internal.call(this);
    try {
      executor(ctx($resolve, this, 1), ctx($reject, this, 1));
    } catch (err) {
      $reject.call(this, err);
    }
  };
  // eslint-disable-next-line no-unused-vars
  Internal = function Promise(executor) {
    this._c = [];             // <- awaiting reactions
    this._a = undefined;      // <- checked in isUnhandled reactions
    this._s = 0;              // <- state
    this._d = false;          // <- done
    this._v = undefined;      // <- value
    this._h = 0;              // <- rejection state, 0 - default, 1 - handled, 2 - unhandled
    this._n = false;          // <- notify
  };
  Internal.prototype = __webpack_require__(43)($Promise.prototype, {
    // 25.4.5.3 Promise.prototype.then(onFulfilled, onRejected)
    then: function then(onFulfilled, onRejected) {
      var reaction = newPromiseCapability(speciesConstructor(this, $Promise));
      reaction.ok = typeof onFulfilled == 'function' ? onFulfilled : true;
      reaction.fail = typeof onRejected == 'function' && onRejected;
      reaction.domain = isNode ? process.domain : undefined;
      this._c.push(reaction);
      if (this._a) this._a.push(reaction);
      if (this._s) notify(this, false);
      return reaction.promise;
    },
    // 25.4.5.1 Promise.prototype.catch(onRejected)
    'catch': function (onRejected) {
      return this.then(undefined, onRejected);
    }
  });
  OwnPromiseCapability = function () {
    var promise = new Internal();
    this.promise = promise;
    this.resolve = ctx($resolve, promise, 1);
    this.reject = ctx($reject, promise, 1);
  };
  newPromiseCapabilityModule.f = newPromiseCapability = function (C) {
    return C === $Promise || C === Wrapper
      ? new OwnPromiseCapability(C)
      : newGenericPromiseCapability(C);
  };
}

$export($export.G + $export.W + $export.F * !USE_NATIVE, { Promise: $Promise });
__webpack_require__(44)($Promise, PROMISE);
__webpack_require__(45)(PROMISE);
Wrapper = __webpack_require__(6)[PROMISE];

// statics
$export($export.S + $export.F * !USE_NATIVE, PROMISE, {
  // 25.4.4.5 Promise.reject(r)
  reject: function reject(r) {
    var capability = newPromiseCapability(this);
    var $$reject = capability.reject;
    $$reject(r);
    return capability.promise;
  }
});
$export($export.S + $export.F * (LIBRARY || !USE_NATIVE), PROMISE, {
  // 25.4.4.6 Promise.resolve(x)
  resolve: function resolve(x) {
    return promiseResolve(LIBRARY && this === Wrapper ? $Promise : this, x);
  }
});
$export($export.S + $export.F * !(USE_NATIVE && __webpack_require__(46)(function (iter) {
  $Promise.all(iter)['catch'](empty);
})), PROMISE, {
  // 25.4.4.1 Promise.all(iterable)
  all: function all(iterable) {
    var C = this;
    var capability = newPromiseCapability(C);
    var resolve = capability.resolve;
    var reject = capability.reject;
    var result = perform(function () {
      var values = [];
      var index = 0;
      var remaining = 1;
      forOf(iterable, false, function (promise) {
        var $index = index++;
        var alreadyCalled = false;
        values.push(undefined);
        remaining++;
        C.resolve(promise).then(function (value) {
          if (alreadyCalled) return;
          alreadyCalled = true;
          values[$index] = value;
          --remaining || resolve(values);
        }, reject);
      });
      --remaining || resolve(values);
    });
    if (result.e) reject(result.v);
    return capability.promise;
  },
  // 25.4.4.4 Promise.race(iterable)
  race: function race(iterable) {
    var C = this;
    var capability = newPromiseCapability(C);
    var reject = capability.reject;
    var result = perform(function () {
      forOf(iterable, false, function (promise) {
        C.resolve(promise).then(capability.resolve, reject);
      });
    });
    if (result.e) reject(result.v);
    return capability.promise;
  }
});


/***/ }),
/* 24 */
/***/ (function(module, exports) {

module.exports = false;


/***/ }),
/* 25 */
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__(0);
var SHARED = '__core-js_shared__';
var store = global[SHARED] || (global[SHARED] = {});
module.exports = function (key) {
  return store[key] || (store[key] = {});
};


/***/ }),
/* 26 */
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__(0);
var core = __webpack_require__(6);
var hide = __webpack_require__(13);
var redefine = __webpack_require__(16);
var ctx = __webpack_require__(4);
var PROTOTYPE = 'prototype';

var $export = function (type, name, source) {
  var IS_FORCED = type & $export.F;
  var IS_GLOBAL = type & $export.G;
  var IS_STATIC = type & $export.S;
  var IS_PROTO = type & $export.P;
  var IS_BIND = type & $export.B;
  var target = IS_GLOBAL ? global : IS_STATIC ? global[name] || (global[name] = {}) : (global[name] || {})[PROTOTYPE];
  var exports = IS_GLOBAL ? core : core[name] || (core[name] = {});
  var expProto = exports[PROTOTYPE] || (exports[PROTOTYPE] = {});
  var key, own, out, exp;
  if (IS_GLOBAL) source = name;
  for (key in source) {
    // contains in native
    own = !IS_FORCED && target && target[key] !== undefined;
    // export native or passed
    out = (own ? target : source)[key];
    // bind timers to global for call from export context
    exp = IS_BIND && own ? ctx(out, global) : IS_PROTO && typeof out == 'function' ? ctx(Function.call, out) : out;
    // extend global
    if (target) redefine(target, key, out, type & $export.U);
    // export
    if (exports[key] != out) hide(exports, key, exp);
    if (IS_PROTO && expProto[key] != out) expProto[key] = out;
  }
};
global.core = core;
// type bitmap
$export.F = 1;   // forced
$export.G = 2;   // global
$export.S = 4;   // static
$export.P = 8;   // proto
$export.B = 16;  // bind
$export.W = 32;  // wrap
$export.U = 64;  // safe
$export.R = 128; // real proto method for `library`
module.exports = $export;


/***/ }),
/* 27 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = !__webpack_require__(7) && !__webpack_require__(14)(function () {
  return Object.defineProperty(__webpack_require__(15)('div'), 'a', { get: function () { return 7; } }).a != 7;
});


/***/ }),
/* 28 */
/***/ (function(module, exports, __webpack_require__) {

// 7.1.1 ToPrimitive(input [, PreferredType])
var isObject = __webpack_require__(3);
// instead of the ES6 spec version, we didn't implement @@toPrimitive case
// and the second argument - flag - preferred type is a string
module.exports = function (it, S) {
  if (!isObject(it)) return it;
  var fn, val;
  if (S && typeof (fn = it.toString) == 'function' && !isObject(val = fn.call(it))) return val;
  if (typeof (fn = it.valueOf) == 'function' && !isObject(val = fn.call(it))) return val;
  if (!S && typeof (fn = it.toString) == 'function' && !isObject(val = fn.call(it))) return val;
  throw TypeError("Can't convert object to primitive value");
};


/***/ }),
/* 29 */
/***/ (function(module, exports) {

module.exports = function (bitmap, value) {
  return {
    enumerable: !(bitmap & 1),
    configurable: !(bitmap & 2),
    writable: !(bitmap & 4),
    value: value
  };
};


/***/ }),
/* 30 */
/***/ (function(module, exports) {

module.exports = function (it, Constructor, name, forbiddenField) {
  if (!(it instanceof Constructor) || (forbiddenField !== undefined && forbiddenField in it)) {
    throw TypeError(name + ': incorrect invocation!');
  } return it;
};


/***/ }),
/* 31 */
/***/ (function(module, exports, __webpack_require__) {

var ctx = __webpack_require__(4);
var call = __webpack_require__(32);
var isArrayIter = __webpack_require__(33);
var anObject = __webpack_require__(2);
var toLength = __webpack_require__(34);
var getIterFn = __webpack_require__(36);
var BREAK = {};
var RETURN = {};
var exports = module.exports = function (iterable, entries, fn, that, ITERATOR) {
  var iterFn = ITERATOR ? function () { return iterable; } : getIterFn(iterable);
  var f = ctx(fn, that, entries ? 2 : 1);
  var index = 0;
  var length, step, iterator, result;
  if (typeof iterFn != 'function') throw TypeError(iterable + ' is not iterable!');
  // fast case for arrays with default iterator
  if (isArrayIter(iterFn)) for (length = toLength(iterable.length); length > index; index++) {
    result = entries ? f(anObject(step = iterable[index])[0], step[1]) : f(iterable[index]);
    if (result === BREAK || result === RETURN) return result;
  } else for (iterator = iterFn.call(iterable); !(step = iterator.next()).done;) {
    result = call(iterator, f, step.value, entries);
    if (result === BREAK || result === RETURN) return result;
  }
};
exports.BREAK = BREAK;
exports.RETURN = RETURN;


/***/ }),
/* 32 */
/***/ (function(module, exports, __webpack_require__) {

// call something on iterator step with safe closing on error
var anObject = __webpack_require__(2);
module.exports = function (iterator, fn, value, entries) {
  try {
    return entries ? fn(anObject(value)[0], value[1]) : fn(value);
  // 7.4.6 IteratorClose(iterator, completion)
  } catch (e) {
    var ret = iterator['return'];
    if (ret !== undefined) anObject(ret.call(iterator));
    throw e;
  }
};


/***/ }),
/* 33 */
/***/ (function(module, exports, __webpack_require__) {

// check on default Array iterator
var Iterators = __webpack_require__(18);
var ITERATOR = __webpack_require__(1)('iterator');
var ArrayProto = Array.prototype;

module.exports = function (it) {
  return it !== undefined && (Iterators.Array === it || ArrayProto[ITERATOR] === it);
};


/***/ }),
/* 34 */
/***/ (function(module, exports, __webpack_require__) {

// 7.1.15 ToLength
var toInteger = __webpack_require__(35);
var min = Math.min;
module.exports = function (it) {
  return it > 0 ? min(toInteger(it), 0x1fffffffffffff) : 0; // pow(2, 53) - 1 == 9007199254740991
};


/***/ }),
/* 35 */
/***/ (function(module, exports) {

// 7.1.4 ToInteger
var ceil = Math.ceil;
var floor = Math.floor;
module.exports = function (it) {
  return isNaN(it = +it) ? 0 : (it > 0 ? floor : ceil)(it);
};


/***/ }),
/* 36 */
/***/ (function(module, exports, __webpack_require__) {

var classof = __webpack_require__(11);
var ITERATOR = __webpack_require__(1)('iterator');
var Iterators = __webpack_require__(18);
module.exports = __webpack_require__(6).getIteratorMethod = function (it) {
  if (it != undefined) return it[ITERATOR]
    || it['@@iterator']
    || Iterators[classof(it)];
};


/***/ }),
/* 37 */
/***/ (function(module, exports, __webpack_require__) {

// 7.3.20 SpeciesConstructor(O, defaultConstructor)
var anObject = __webpack_require__(2);
var aFunction = __webpack_require__(5);
var SPECIES = __webpack_require__(1)('species');
module.exports = function (O, D) {
  var C = anObject(O).constructor;
  var S;
  return C === undefined || (S = anObject(C)[SPECIES]) == undefined ? D : aFunction(S);
};


/***/ }),
/* 38 */
/***/ (function(module, exports) {

// fast apply, http://jsperf.lnkit.com/fast-apply/5
module.exports = function (fn, args, that) {
  var un = that === undefined;
  switch (args.length) {
    case 0: return un ? fn()
                      : fn.call(that);
    case 1: return un ? fn(args[0])
                      : fn.call(that, args[0]);
    case 2: return un ? fn(args[0], args[1])
                      : fn.call(that, args[0], args[1]);
    case 3: return un ? fn(args[0], args[1], args[2])
                      : fn.call(that, args[0], args[1], args[2]);
    case 4: return un ? fn(args[0], args[1], args[2], args[3])
                      : fn.call(that, args[0], args[1], args[2], args[3]);
  } return fn.apply(that, args);
};


/***/ }),
/* 39 */
/***/ (function(module, exports, __webpack_require__) {

var document = __webpack_require__(0).document;
module.exports = document && document.documentElement;


/***/ }),
/* 40 */
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__(0);
var macrotask = __webpack_require__(19).set;
var Observer = global.MutationObserver || global.WebKitMutationObserver;
var process = global.process;
var Promise = global.Promise;
var isNode = __webpack_require__(8)(process) == 'process';

module.exports = function () {
  var head, last, notify;

  var flush = function () {
    var parent, fn;
    if (isNode && (parent = process.domain)) parent.exit();
    while (head) {
      fn = head.fn;
      head = head.next;
      try {
        fn();
      } catch (e) {
        if (head) notify();
        else last = undefined;
        throw e;
      }
    } last = undefined;
    if (parent) parent.enter();
  };

  // Node.js
  if (isNode) {
    notify = function () {
      process.nextTick(flush);
    };
  // browsers with MutationObserver, except iOS Safari - https://github.com/zloirock/core-js/issues/339
  } else if (Observer && !(global.navigator && global.navigator.standalone)) {
    var toggle = true;
    var node = document.createTextNode('');
    new Observer(flush).observe(node, { characterData: true }); // eslint-disable-line no-new
    notify = function () {
      node.data = toggle = !toggle;
    };
  // environments with maybe non-completely correct, but existent Promise
  } else if (Promise && Promise.resolve) {
    var promise = Promise.resolve();
    notify = function () {
      promise.then(flush);
    };
  // for other environments - macrotask based on:
  // - setImmediate
  // - MessageChannel
  // - window.postMessag
  // - onreadystatechange
  // - setTimeout
  } else {
    notify = function () {
      // strange IE + webpack dev server bug - use .call(global)
      macrotask.call(global, flush);
    };
  }

  return function (fn) {
    var task = { fn: fn, next: undefined };
    if (last) last.next = task;
    if (!head) {
      head = task;
      notify();
    } last = task;
  };
};


/***/ }),
/* 41 */
/***/ (function(module, exports) {

module.exports = function (exec) {
  try {
    return { e: false, v: exec() };
  } catch (e) {
    return { e: true, v: e };
  }
};


/***/ }),
/* 42 */
/***/ (function(module, exports, __webpack_require__) {

var anObject = __webpack_require__(2);
var isObject = __webpack_require__(3);
var newPromiseCapability = __webpack_require__(20);

module.exports = function (C, x) {
  anObject(C);
  if (isObject(x) && x.constructor === C) return x;
  var promiseCapability = newPromiseCapability.f(C);
  var resolve = promiseCapability.resolve;
  resolve(x);
  return promiseCapability.promise;
};


/***/ }),
/* 43 */
/***/ (function(module, exports, __webpack_require__) {

var redefine = __webpack_require__(16);
module.exports = function (target, src, safe) {
  for (var key in src) redefine(target, key, src[key], safe);
  return target;
};


/***/ }),
/* 44 */
/***/ (function(module, exports, __webpack_require__) {

var def = __webpack_require__(9).f;
var has = __webpack_require__(17);
var TAG = __webpack_require__(1)('toStringTag');

module.exports = function (it, tag, stat) {
  if (it && !has(it = stat ? it : it.prototype, TAG)) def(it, TAG, { configurable: true, value: tag });
};


/***/ }),
/* 45 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var global = __webpack_require__(0);
var dP = __webpack_require__(9);
var DESCRIPTORS = __webpack_require__(7);
var SPECIES = __webpack_require__(1)('species');

module.exports = function (KEY) {
  var C = global[KEY];
  if (DESCRIPTORS && C && !C[SPECIES]) dP.f(C, SPECIES, {
    configurable: true,
    get: function () { return this; }
  });
};


/***/ }),
/* 46 */
/***/ (function(module, exports, __webpack_require__) {

var ITERATOR = __webpack_require__(1)('iterator');
var SAFE_CLOSING = false;

try {
  var riter = [7][ITERATOR]();
  riter['return'] = function () { SAFE_CLOSING = true; };
  // eslint-disable-next-line no-throw-literal
  Array.from(riter, function () { throw 2; });
} catch (e) { /* empty */ }

module.exports = function (exec, skipClosing) {
  if (!skipClosing && !SAFE_CLOSING) return false;
  var safe = false;
  try {
    var arr = [7];
    var iter = arr[ITERATOR]();
    iter.next = function () { return { done: safe = true }; };
    arr[ITERATOR] = function () { return iter; };
    exec(arr);
  } catch (e) { /* empty */ }
  return safe;
};


/***/ }),
/* 47 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__src_bootstrap__ = __webpack_require__(48);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__src_bootstrap___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__src_bootstrap__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__less_edit_less__ = __webpack_require__(54);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__less_edit_less___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__less_edit_less__);



/***/ }),
/* 48 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", { value: true });
__webpack_require__(49);
var ajax_1 = __webpack_require__(50);
var drupal_1 = __webpack_require__(51);
var state_1 = __webpack_require__(21);
if (window.Drupal) {
    var state_2;
    Drupal.behaviors.Layout = {
        attach: function (context, settings) {
            if (!settings.layout) {
                settings.layout = {};
            }
            if (!state_2) {
                state_2 = new drupal_1.DrupalState(new ajax_1.AjaxLayoutHandler(settings.basePath, settings.layout.destination));
            }
            state_2.initNoBehaviors(context);
        }
    };
} else {
    var state = new state_1.State(new ajax_1.AjaxLayoutHandler('/', 'destinationwtf'));
    state.init(document.body);
}

/***/ }),
/* 49 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/***/ }),
/* 50 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var __awaiter = this && this.__awaiter || function (thisArg, _arguments, P, generator) {
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) {
            try {
                step(generator.next(value));
            } catch (e) {
                reject(e);
            }
        }
        function rejected(value) {
            try {
                step(generator["throw"](value));
            } catch (e) {
                reject(e);
            }
        }
        function step(result) {
            result.done ? resolve(result.value) : new P(function (resolve) {
                resolve(result.value);
            }).then(fulfilled, rejected);
        }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = this && this.__generator || function (thisArg, body) {
    var _ = { label: 0, sent: function () {
            if (t[0] & 1) throw t[1];return t[1];
        }, trys: [], ops: [] },
        f,
        y,
        t,
        g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function () {
        return this;
    }), g;
    function verb(n) {
        return function (v) {
            return step([n, v]);
        };
    }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (_) try {
            if (f = 1, y && (t = y[op[0] & 2 ? "return" : op[0] ? "throw" : "next"]) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [0, t.value];
            switch (op[0]) {
                case 0:case 1:
                    t = op;break;
                case 4:
                    _.label++;return { value: op[1], done: false };
                case 5:
                    _.label++;y = op[1];op = [0];continue;
                case 7:
                    op = _.ops.pop();_.trys.pop();continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) {
                        _ = 0;continue;
                    }
                    if (op[0] === 3 && (!t || op[1] > t[0] && op[1] < t[3])) {
                        _.label = op[1];break;
                    }
                    if (op[0] === 6 && _.label < t[1]) {
                        _.label = t[1];t = op;break;
                    }
                    if (t && _.label < t[2]) {
                        _.label = t[2];_.ops.push(op);break;
                    }
                    if (t[2]) _.ops.pop();
                    _.trys.pop();continue;
            }
            op = body.call(thisArg, _);
        } catch (e) {
            op = [6, e];y = 0;
        } finally {
            f = t = 0;
        }
        if (op[0] & 5) throw op[1];return { value: op[0] ? op[1] : void 0, done: true };
    }
};
Object.defineProperty(exports, "__esModule", { value: true });
var item_1 = __webpack_require__(10);
var AjaxRoute;
(function (AjaxRoute) {
    AjaxRoute["Add"] = "layout/ajax/add-item";
    AjaxRoute["AddColumn"] = "layout/ajax/add-column";
    AjaxRoute["AddColumnContainer"] = "layout/ajax/add-column-container";
    AjaxRoute["GetAllowedStyles"] = "layout/ajax/get-styles";
    AjaxRoute["Move"] = "layout/ajax/move";
    AjaxRoute["Remove"] = "layout/ajax/remove";
    AjaxRoute["Render"] = "layout/ajax/render";
    AjaxRoute["SetStyle"] = "layout/ajax/set-style";
})(AjaxRoute || (AjaxRoute = {}));
var AjaxLayoutHandler = function () {
    function AjaxLayoutHandler(baseUrl, destination) {
        this.baseUrl = baseUrl;
        this.destination = destination;
    }
    AjaxLayoutHandler.prototype.buildFormData = function (data) {
        var formData = new FormData();
        for (var key in data) {
            formData.append(key, data[key]);
        }
        return formData;
    };
    AjaxLayoutHandler.prototype.request = function (route, data) {
        var _this = this;
        return new Promise(function (resolve, reject) {
            var req = new XMLHttpRequest();
            req.open('POST', _this.baseUrl + route);
            req.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            req.addEventListener("load", function () {
                if (this.status !== 200) {
                    reject(this.status + ": " + this.statusText);
                } else {
                    resolve(req);
                }
            });
            req.addEventListener("error", function () {
                reject(this.status + ": " + this.statusText);
            });
            req.send(_this.buildFormData(data));
        });
    };
    AjaxLayoutHandler.prototype.createElementFromResponse = function (req) {
        var data = JSON.parse(req.responseText);
        if (!data || !data.success || !data.output) {
            throw req.status + ": " + req.statusText + ": got invalid response data";
        }
        var element = document.createElement('div');
        element.innerHTML = data.output;
        if (!(element.firstElementChild instanceof Element)) {
            throw req.status + ": " + req.statusText + ": got invalid response html output";
        }
        return element.firstElementChild;
    };
    AjaxLayoutHandler.prototype.debug = function (message) {
        console.log("layout error: " + message);
    };
    AjaxLayoutHandler.prototype.addColumn = function (token, layout, containerId, position) {
        return __awaiter(this, void 0, void 0, function () {
            var req;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        return [4, this.request(AjaxRoute.AddColumn, {
                            token: token,
                            layout: layout,
                            containerId: containerId,
                            position: position || 0,
                            destination: this.destination
                        })];
                    case 1:
                        req = _a.sent();
                        return [2, this.createElementFromResponse(req)];
                }
            });
        });
    };
    AjaxLayoutHandler.prototype.addColumnContainer = function (token, layout, containerId, position, columnCount, style) {
        return __awaiter(this, void 0, void 0, function () {
            var req;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        return [4, this.request(AjaxRoute.AddColumnContainer, {
                            token: token,
                            layout: layout,
                            containerId: containerId,
                            position: position,
                            columnCount: columnCount || 2,
                            style: style || item_1.Item.DefaultStyle,
                            destination: this.destination
                        })];
                    case 1:
                        req = _a.sent();
                        return [2, this.createElementFromResponse(req)];
                }
            });
        });
    };
    AjaxLayoutHandler.prototype.addItem = function (token, layout, containerId, itemType, itemId, position, style) {
        return __awaiter(this, void 0, void 0, function () {
            var req;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        return [4, this.request(AjaxRoute.Add, {
                            token: token,
                            layout: layout,
                            containerId: containerId,
                            itemType: itemType,
                            itemId: itemId,
                            position: position,
                            style: style || item_1.Item.DefaultStyle,
                            destination: this.destination
                        })];
                    case 1:
                        req = _a.sent();
                        return [2, this.createElementFromResponse(req)];
                }
            });
        });
    };
    AjaxLayoutHandler.prototype.getAllowedStyles = function (token, layout, itemId) {
        return __awaiter(this, void 0, void 0, function () {
            var req, data, ret;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        return [4, this.request(AjaxRoute.GetAllowedStyles, {
                            token: token,
                            layout: layout,
                            itemId: itemId,
                            destination: this.destination
                        })];
                    case 1:
                        req = _a.sent();
                        data = JSON.parse(req.responseText);
                        if (!data || !data.success || !data.styles) {
                            throw req.status + ": " + req.statusText + ": got invalid response data";
                        }
                        ret = { current: data.current || null, styles: data.styles };
                        return [2, ret];
                }
            });
        });
    };
    AjaxLayoutHandler.prototype.moveItem = function (token, layout, containerId, itemId, newPosition) {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        return [4, this.request(AjaxRoute.Move, {
                            token: token,
                            layout: layout,
                            containerId: containerId,
                            itemId: itemId,
                            newPosition: newPosition,
                            destination: this.destination
                        })];
                    case 1:
                        _a.sent();
                        return [2];
                }
            });
        });
    };
    AjaxLayoutHandler.prototype.removeItem = function (token, layout, itemId) {
        return __awaiter(this, void 0, void 0, function () {
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        return [4, this.request(AjaxRoute.Remove, {
                            token: token,
                            layout: layout,
                            itemId: itemId,
                            destination: this.destination
                        })];
                    case 1:
                        _a.sent();
                        return [2];
                }
            });
        });
    };
    AjaxLayoutHandler.prototype.renderItem = function (token, layout, itemId) {
        return __awaiter(this, void 0, void 0, function () {
            var req;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        return [4, this.request(AjaxRoute.Render, {
                            token: token,
                            layout: layout,
                            itemId: itemId,
                            destination: this.destination
                        })];
                    case 1:
                        req = _a.sent();
                        return [2, this.createElementFromResponse(req)];
                }
            });
        });
    };
    AjaxLayoutHandler.prototype.setStyle = function (token, layout, itemId, style) {
        return __awaiter(this, void 0, void 0, function () {
            var req;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        return [4, this.request(AjaxRoute.SetStyle, {
                            token: token,
                            layout: layout,
                            itemId: itemId,
                            style: style || null,
                            destination: this.destination
                        })];
                    case 1:
                        req = _a.sent();
                        return [2, this.createElementFromResponse(req)];
                }
            });
        });
    };
    return AjaxLayoutHandler;
}();
exports.AjaxLayoutHandler = AjaxLayoutHandler;

/***/ }),
/* 51 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var __extends = this && this.__extends || function () {
    var extendStatics = Object.setPrototypeOf || { __proto__: [] } instanceof Array && function (d, b) {
        d.__proto__ = b;
    } || function (d, b) {
        for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    };
    return function (d, b) {
        extendStatics(d, b);
        function __() {
            this.constructor = d;
        }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
}();
Object.defineProperty(exports, "__esModule", { value: true });
var state_1 = __webpack_require__(21);
var DrupalState = function (_super) {
    __extends(DrupalState, _super);
    function DrupalState() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    DrupalState.prototype.init = function (context) {
        _super.prototype.init.call(this, context);
        Drupal.attachBehaviors(context);
    };
    DrupalState.prototype.translate = function (text, variables) {
        return Drupal.t(text, variables);
    };
    DrupalState.prototype.initNoBehaviors = function (context) {
        _super.prototype.init.call(this, context);
    };
    return DrupalState;
}(state_1.State);
exports.DrupalState = DrupalState;

/***/ }),
/* 52 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", { value: true });
var item_1 = __webpack_require__(10);
var dialog_1 = __webpack_require__(53);
var ICON_TEMPLATE = "<span class=\"fa fa-__GLYPH__\" aria-hidden=\"true\"></span> ";
var DRAG_TEMPLATE = "<span role=\"drag\" title=\"Maintain left mouse button to move\">\n  <span class=\"fa fa-arrows\" aria-hidden=\"true\"></span>\n</span>";
var MENU_TEMPLATE = "<div class=\"layout-menu\" data-menu=\"1\">\n  <a role=\"button\" href=\"#\" title=\"Click to open, double-click to expand/hide content\">\n    <span class=\"fa fa-cog\" aria-hidden=\"true\"></span>\n    <span class=\"title\">__TITLE__</span>\n  </a>\n  <ul></ul>\n</div>";
var globalMenuRegistry = [];
var globalDocumentListenerSet = false;
function globalDocumentCloseMenuListener(event) {
    if (globalMenuRegistry.length) {
        for (var _i = 0, globalMenuRegistry_1 = globalMenuRegistry; _i < globalMenuRegistry_1.length; _i++) {
            var menu = globalMenuRegistry_1[_i];
            if (!(event.target instanceof Node) || !menu.element.contains(event.target)) {
                menu.close();
            }
        }
    }
}
function globalDocumentCloseAllMenu() {
    if (globalMenuRegistry.length) {
        for (var _i = 0, globalMenuRegistry_2 = globalMenuRegistry; _i < globalMenuRegistry_2.length; _i++) {
            var menu = globalMenuRegistry_2[_i];
            menu.close();
        }
    }
}
var Menu = function () {
    function Menu(item, element) {
        var _this = this;
        this.item = item;
        this.element = element;
        this.master = element.querySelector("a");
        this.master.addEventListener("dblclick", function (event) {
            event.stopPropagation();
            _this.item.toggleCollapse();
        });
        this.master.addEventListener("click", function (event) {
            event.preventDefault();
            _this.open();
        });
    }
    Menu.prototype.close = function () {
        var dropdown = this.element.querySelector("ul");
        if (dropdown) {
            dropdown.style.display = "none";
        }
    };
    Menu.prototype.open = function () {
        var dropdown = this.element.querySelector("ul");
        if (dropdown) {
            dropdown.style.display = "block";
        }
    };
    return Menu;
}();
function createLink(state, text, icon, callback) {
    var menuItem = document.createElement("li");
    var link = document.createElement("a");
    link.setAttribute("href", "#");
    link.setAttribute("role", "button");
    if (icon) {
        link.innerHTML += ICON_TEMPLATE.replace("__GLYPH__", icon);
    }
    link.innerHTML += text;
    link.addEventListener("click", function (event) {
        event.preventDefault();
        event.stopPropagation();
        globalDocumentCloseAllMenu();
        callback(event).then(function (_) {}).catch(function (error) {
            state.handler.debug(error);
        });
    });
    menuItem.appendChild(link);
    return menuItem;
}
function createDivider() {
    var divider = document.createElement('li');
    divider.setAttribute("class", "divider");
    divider.setAttribute("role", "separator");
    return divider;
}
function createItemLinks(state, item) {
    var links = [];
    var parent = item.getParentContainer();
    links.push(createLink(state, state.translate("Change style"), "wrench", function (event) {
        var currentSelection;
        var hasChanged = false;
        return state.handler.getAllowedStyles(parent.token, parent.layoutId, item.id).then(function (styleList) {
            var content = document.createElement("form");
            var select = document.createElement("select");
            content.appendChild(select);
            var hasDefault = false;
            for (var style in styleList.styles) {
                var option = document.createElement("option");
                option.value = style;
                option.innerHTML = styleList.styles[style];
                select.appendChild(option);
                if (style === item_1.Item.DefaultStyle) {
                    hasDefault = true;
                }
                if (styleList.current && style === styleList.current) {
                    option.selected = true;
                }
            }
            if (!hasDefault) {
                var option = document.createElement("option");
                option.value = item_1.Item.DefaultStyle;
                option.innerHTML = state.translate("Default");
                select.insertBefore(option, select.firstElementChild);
            }
            select.addEventListener("change", function () {
                currentSelection = select.value;
                hasChanged = true;
            });
            dialog_1.createModal(state.translate("Set style"), content, event.pageX, event.pageY).then(function () {
                if (hasChanged) {
                    return state.handler.setStyle(parent.token, parent.layoutId, item.id, currentSelection).then(function (element) {
                        item.element.parentElement.replaceChild(element, item.element);
                        state.init(element);
                        state.initItem(element, parent);
                    });
                }
            });
        });
    }));
    links.push(createDivider());
    links.push(createLink(state, state.translate("Remove"), "remove", function () {
        return state.handler.removeItem(parent.token, parent.layoutId, item.id).then(function () {
            state.remove(item.element);
        });
    }));
    return links;
}
function createHorizontalLinks(state, container) {
    var links = [];
    links.push(createLink(state, state.translate("Add column to left"), "chevron-left", function () {
        return state.handler.addColumn(container.token, container.layoutId, container.id, 0).then(function (element) {
            container.element.insertBefore(element, container.element.firstChild);
            state.init(element);
            state.initContainer(element, container);
        });
    }));
    links.push(createLink(state, state.translate("Add column to right"), "chevron-right", function () {
        var position = item_1.getContainerCount(container.element);
        return state.handler.addColumn(container.token, container.layoutId, container.id, position).then(function (element) {
            container.element.appendChild(element);
            state.init(element);
            state.initContainer(element, container);
        });
    }));
    links.push(createDivider());
    links.push(createLink(state, state.translate("Remove"), "remove", function () {
        return state.handler.removeItem(container.token, container.layoutId, container.id).then(function () {
            state.remove(container.element);
        });
    }));
    return links;
}
function createLayoutLinks(state, container) {
    var links = [];
    links.push(createLink(state, state.translate("Add columns to top"), "columns", function () {
        return state.handler.addColumnContainer(container.token, container.layoutId, container.id, 0).then(function (element) {
            container.element.insertBefore(element, container.element.firstChild);
            state.init(element);
            state.initContainer(element, container);
        });
    }));
    links.push(createLink(state, state.translate("Add columns to bottom"), "columns", function () {
        var position = item_1.getContainerCount(container.element);
        return state.handler.addColumn(container.token, container.layoutId, container.id, position).then(function (element) {
            container.element.appendChild(element);
            state.init(element);
            state.initContainer(element, container);
        });
    }));
    return links;
}
function createColumnLinks(state, container) {
    var links = createLayoutLinks(state, container);
    var parent = container.getParentContainer();
    links.push(createDivider());
    links.push(createLink(state, state.translate("Change style"), "wrench", function (event) {
        var currentSelection;
        var hasChanged = false;
        return state.handler.getAllowedStyles(parent.token, parent.layoutId, container.id).then(function (styleList) {
            var content = document.createElement("form");
            var select = document.createElement("select");
            content.appendChild(select);
            var hasDefault = false;
            for (var style in styleList.styles) {
                var option = document.createElement("option");
                option.value = style;
                option.innerHTML = styleList.styles[style];
                select.appendChild(option);
                if (style === item_1.Item.DefaultStyle) {
                    hasDefault = true;
                }
                if (styleList.current && style === styleList.current) {
                    option.selected = true;
                }
            }
            if (!hasDefault) {
                var option = document.createElement("option");
                option.value = item_1.Item.DefaultStyle;
                option.innerHTML = state.translate("Default");
                select.insertBefore(option, select.firstElementChild);
            }
            select.addEventListener("change", function () {
                currentSelection = select.value;
                hasChanged = true;
            });
            dialog_1.createModal(state.translate("Set style"), content, event.pageX, event.pageY).then(function () {
                if (hasChanged) {
                    return state.handler.setStyle(parent.token, parent.layoutId, container.id, currentSelection).then(function (element) {
                        container.element.parentElement.replaceChild(element, container.element);
                        state.init(element);
                        state.initContainer(element, parent);
                    });
                }
            });
        });
    }));
    links.push(createDivider());
    links.push(createLink(state, state.translate("Add column before"), "chevron-left", function () {
        return state.handler.addColumn(parent.token, parent.layoutId, parent.id, container.getPosition()).then(function (element) {
            parent.element.insertBefore(element, container.element);
            state.init(element);
            state.initContainer(element, parent);
        });
    }));
    links.push(createLink(state, state.translate("Add column after"), "chevron-right", function () {
        return state.handler.addColumn(parent.token, parent.layoutId, parent.id, container.getPosition() + 1).then(function (element) {
            parent.element.insertBefore(element, container.element.nextSibling);
            state.init(element);
            state.initContainer(element, parent);
        });
    }));
    links.push(createDivider());
    links.push(createLink(state, state.translate("Remove this column"), "remove", function () {
        return state.handler.removeItem(container.token, container.layoutId, container.id).then(function () {
            state.remove(container.element);
        });
    }));
    return links;
}
function createMenu(state, item) {
    var links = [];
    var title = state.translate("Error");
    var addDragIcon = false;
    if (item instanceof item_1.Container) {
        if (item.type === item_1.ContainerType.Column) {
            title = state.translate("Column");
            links = createColumnLinks(state, item);
        } else if (item.type === item_1.ContainerType.Horizontal) {
            title = state.translate("Columns container");
            links = createHorizontalLinks(state, item);
            addDragIcon = true;
        } else {
            title = state.translate("Layout");
            links = createLayoutLinks(state, item);
        }
    } else {
        title = state.translate("Item");
        links = createItemLinks(state, item);
        addDragIcon = true;
    }
    var output = MENU_TEMPLATE.replace(new RegExp('__TITLE__', 'g'), title).replace("__LINKS__", "<li><a>coucou</a></href>");
    var element = document.createElement('div');
    element.innerHTML = output;
    var parentElement = element.firstElementChild;
    var menuList = parentElement.querySelector("ul");
    for (var _i = 0, links_1 = links; _i < links_1.length; _i++) {
        var link = links_1[_i];
        menuList.appendChild(link);
    }
    if (!globalDocumentListenerSet) {
        document.addEventListener("click", function (event) {
            globalDocumentCloseMenuListener(event);
        });
        globalDocumentListenerSet = true;
    }
    globalMenuRegistry.push(new Menu(item, parentElement));
    item.element.insertBefore(parentElement, item.element.firstChild);
}
exports.createMenu = createMenu;

/***/ }),
/* 53 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", { value: true });
var DIALOG_TEMPLATE = "<div class=\"layout-modal\" tabindex=\"-1\" role=\"dialog\">\n  <div role=\"document\">\n    <button name=\"close\" type=\"button\" aria-label=\"Close\">\n      <span aria-hidden=\"true\">&times;</span>\n    </button>\n    <h4 class=\"modal-title\">__TITLE__</h4>\n    <div id=\"content\"></div>\n  </div>\n</div>";
function createModal(title, content, posX, posY) {
    return new Promise(function (resolve, reject) {
        var temp = document.createElement('div');
        temp.innerHTML = DIALOG_TEMPLATE.replace(new RegExp('__TITLE__', 'g'), title);
        var dialog = temp.firstElementChild;
        var placeholder = dialog.querySelector("#content");
        placeholder.parentElement.replaceChild(content, placeholder);
        document.body.appendChild(dialog);
        dialog.style.display = "block";
        dialog.style.position = "absolute";
        dialog.style.left = posX.toString() + "px";
        dialog.style.top = posY.toString() + "px";
        dialog.style.transform = "translate(-50%, -50%)";
        dialog.classList.add("open");
        dialog.querySelector("button[name=close]").addEventListener("click", function (event) {
            event.preventDefault();
            dialog.remove();
            resolve();
        });
    });
}
exports.createModal = createModal;

/***/ }),
/* 54 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ })
/******/ ]);