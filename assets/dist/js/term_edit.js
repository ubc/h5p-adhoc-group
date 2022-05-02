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
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
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
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./assets/src/js/term_edit.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./assets/src/css/term_edit.scss":
/*!***************************************!*\
  !*** ./assets/src/css/term_edit.scss ***!
  \***************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

// extracted by mini-css-extract-plugin

/***/ }),

/***/ "./assets/src/js/term_edit.js":
/*!************************************!*\
  !*** ./assets/src/js/term_edit.js ***!
  \************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _css_term_edit_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../css/term_edit.scss */ "./assets/src/css/term_edit.scss");
/* harmony import */ var _css_term_edit_scss__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_css_term_edit_scss__WEBPACK_IMPORTED_MODULE_0__);


(function ($) {
  $(document).ready(function () {
    $('#add_user').on('click', function (e) {
      e.preventDefault();
      const email = $('#user-email').val(); // Validate input

      var EmailRegex = /^[A-Z0-9._%+-]+@([A-Z0-9-]+\.)+[A-Z]{2,4}$/i;

      if (!email || !EmailRegex.test(email)) {
        setMessage('invalid', 'Email address is not valid.');
        return;
      }

      clearMessage();
      jQuery.ajax({
        type: "post",
        dataType: "json",
        url: ajaxurl,
        data: {
          action: "ubc_h5p_add_user_to_group",
          user_email: email,
          term_id: ubc_h5p_adhocgroup.term_id,
          nonce: ubc_h5p_adhocgroup.security_nonce
        },
        success: function (response) {
          setMessage(response.data.status, response.data.message);

          if (response.data.status === 'valid') {
            window.location.reload();
          }
        }
      });
    });
    $('.delete_user').on('click', function (e) {
      e.preventDefault();
      const username = $(this).closest('tr').find('td:first-child').html();

      if (!confirm("Are you sure to remove " + username + ' from the group?')) {
        return;
      }

      const user_id = $(this).attr('user_id');
      clearMessage();
      jQuery.ajax({
        type: "post",
        dataType: "json",
        url: ajaxurl,
        data: {
          action: "ubc_h5p_delete_user_from_group",
          user_id: user_id,
          term_id: ubc_h5p_adhocgroup.term_id,
          nonce: ubc_h5p_adhocgroup.security_nonce
        },
        success: function (response) {
          window.location.reload();
        }
      });
    });

    function setMessage(status, message) {
      $('#message').html(message).attr('status', status);
    }

    function clearMessage() {
      $('#message').html('');
    }
  });
})(jQuery);

/***/ })

/******/ });
//# sourceMappingURL=term_edit.js.map