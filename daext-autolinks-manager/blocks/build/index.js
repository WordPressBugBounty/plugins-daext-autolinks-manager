/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/automatic-links-options/components/Sidebar.js":
/*!***********************************************************!*\
  !*** ./src/automatic-links-options/components/Sidebar.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);

const {
  SelectControl
} = wp.components;
const {
  useSelect,
  useDispatch
} = wp.data;
const {
  PluginDocumentSettingPanel
} = wp.editor;
const {
  __
} = wp.i18n;
const META_KEY = '_daextam_enable_autolinks';
const Sidebar = () => {
  /*
   * Read the post meta with a subscription to the "core/editor" store.
   *
   * Note that the post meta should not be read only once when the component is mounted (e.g. with
   * "componentDidMount"), because at that time the post entity record might not be available yet, and in that
   * situation "getEditedPostAttribute( 'meta' )" returns "undefined".
   */
  const meta = useSelect(select => select('core/editor').getEditedPostAttribute('meta'), []);
  const {
    editPost
  } = useDispatch('core/editor');

  // Do not render anything if the user does not have the required capability.
  if (parseInt(window.DAEXTAM_PARAMETERS.user_has_interlinks_options_mb_required_capability, 10) !== 1) {
    return null;
  }

  // Do not render anything if this editor tool is not enabled in this post type.
  if (parseInt(window.DAEXTAM_PARAMETERS.interlinks_options_is_active_in_post_type, 10) !== 1) {
    return null;
  }

  // Fall back to the default value defined in the plugin options when the meta is empty or not available yet.
  const metaValue = meta ? meta[META_KEY] : undefined;
  const enableAutolinks = metaValue === '' || metaValue === undefined || metaValue === null ? window.DAEXTAM_PARAMETERS.advanced_enable_autolinks : metaValue;
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(PluginDocumentSettingPanel, {
    name: "daextam-automatic-links-options",
    title: __('Automatic Links', 'daext-autolinks-manager')
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(SelectControl, {
    label: __('Enable', 'daext-autolinks-manager'),
    help: __('Automatically add links based on the configured keywords.', 'daext-autolinks-manager'),
    value: enableAutolinks,
    options: [{
      label: __('No', 'daext-autolinks-manager'),
      value: '0'
    }, {
      label: __('Yes', 'daext-autolinks-manager'),
      value: '1'
    }],
    onChange: value => {
      editPost({
        meta: {
          [META_KEY]: value
        }
      });
    },
    __nextHasNoMarginBottom: true,
    __next40pxDefaultSize: true
  }));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Sidebar);

/***/ }),

/***/ "./src/automatic-links-options/index.js":
/*!**********************************************!*\
  !*** ./src/automatic-links-options/index.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _components_Sidebar__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./components/Sidebar */ "./src/automatic-links-options/components/Sidebar.js");
const {
  registerPlugin
} = wp.plugins;

registerPlugin('daextam-automatic-links-options', {
  icon: false,
  render: _components_Sidebar__WEBPACK_IMPORTED_MODULE_0__["default"]
});

/***/ }),

/***/ "./src/interlinks-optimization/components/Sidebar.js":
/*!***********************************************************!*\
  !*** ./src/interlinks-optimization/components/Sidebar.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _shared_get_post_id__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../shared/get-post-id */ "./src/shared/get-post-id.js");


const {
  Button
} = wp.components;
const {
  PluginDocumentSettingPanel
} = wp.editor;
const {
  useState,
  useEffect
} = wp.element;
const {
  __
} = wp.i18n;
const apiFetch = wp.apiFetch;
const Sidebar = () => {
  const [optimizationData, setOptimizationData] = useState(null);

  // Fetch interlinks optimization data when the component mounts and on post save.
  useEffect(() => {
    const fetchData = () => {
      const postId = (0,_shared_get_post_id__WEBPACK_IMPORTED_MODULE_1__["default"])();

      // Do not perform the request if the post ID is not available.
      if (!postId) {
        return;
      }
      wp.apiFetch({
        path: '/daext-autolinks-manager/v1/generate-interlinks-optimization',
        method: 'POST',
        data: {
          id: postId
        }
      }).then(response => {
        setOptimizationData(response);
      }).catch(error => {
        console.error('Error fetching interlinks optimization data:', error);
      });
    };

    // Initial fetch
    fetchData();
    let wasSaving = false;
    const unsubscribe = wp.data.subscribe(() => {
      const isSaving = wp.data.select('core/editor').isSavingPost();
      const isAutosaving = wp.data.select('core/editor').isAutosavingPost();

      // Detect when a manual save completes
      if (wasSaving && !isSaving && !isAutosaving) {
        fetchData(); // Re-fetch optimization data
      }
      wasSaving = isSaving;
    });
    return () => {
      unsubscribe();
    };
  }, []);

  // Do not render anything if the user does not have the required capability.
  if (parseInt(window.DAEXTAM_PARAMETERS.user_has_interlinks_optimization_mb_required_capability, 10) !== 1) {
    return null;
  }

  // Do not render anything if this editor tool is not enabled in this post type.
  if (parseInt(window.DAEXTAM_PARAMETERS.interlinks_optimization_is_active_in_post_type, 10) !== 1) {
    return null;
  }
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(PluginDocumentSettingPanel, {
    name: "daextam-interlinks-optimization",
    title: __('Internal Links Optimization', 'daext-autolinks-manager')
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "daextam-container"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "daextam-meta-message"
  }, optimizationData ? (() => {
    const totalNumberOfInterlinks = optimizationData['total_number_of_interlinks'];
    const numberOfManualInterlinks = optimizationData['number_of_manual_interlinks'];
    const numberOfAutoInterlinks = optimizationData['number_of_autolinks'];
    const suggestedMin = optimizationData['suggested_min_number_of_interlinks'];
    const suggestedMax = optimizationData['suggested_max_number_of_interlinks'];
    return totalNumberOfInterlinks >= suggestedMin && totalNumberOfInterlinks <= suggestedMax ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, __('The number of internal links in this post is within the recommended range.', 'daext-autolinks-manager')) : (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, __('This post currently contains', 'daext-autolinks-manager'), "\xA0", totalNumberOfInterlinks, "\xA0", totalNumberOfInterlinks === 1 ? __('internal link', 'daext-autolinks-manager') : __('internal links', 'daext-autolinks-manager'), ". (", numberOfManualInterlinks, "\xA0", numberOfManualInterlinks === 1 ? __('manual internal link', 'daext-autolinks-manager') : __('manual internal links', 'daext-autolinks-manager'), "\xA0", __('and', 'daext-autolinks-manager'), "\xA0", numberOfAutoInterlinks, "\xA0", numberOfAutoInterlinks === 1 ? __('auto internal link', 'daext-autolinks-manager') : __('auto internal links', 'daext-autolinks-manager'), ")"), suggestedMin === suggestedMax ? (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, __('Based on the content length and your settings, the recommended number is', 'daext-autolinks-manager'), "\xA0", suggestedMin, ".") : (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null, __('Based on the content length and your settings, the recommended number is between', 'daext-autolinks-manager'), "\xA0", suggestedMin, "\xA0", __('and', 'daext-autolinks-manager'), "\xA0", suggestedMax, "."));
  })() : (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("p", null))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Sidebar);

/***/ }),

/***/ "./src/interlinks-optimization/index.js":
/*!**********************************************!*\
  !*** ./src/interlinks-optimization/index.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _components_Sidebar__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./components/Sidebar */ "./src/interlinks-optimization/components/Sidebar.js");
const {
  registerPlugin
} = wp.plugins;

registerPlugin('daextam-interlinks-optimization', {
  icon: false,
  render: _components_Sidebar__WEBPACK_IMPORTED_MODULE_0__["default"]
});

/***/ }),

/***/ "./src/shared/get-post-id.js":
/*!***********************************!*\
  !*** ./src/shared/get-post-id.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ getPostId)
/* harmony export */ });
/**
 * Returns the ID of the post currently edited in the block editor.
 *
 * The ID is retrieved from the "core/editor" store, and when the store is not ready yet (which can happen while the
 * components of the sidebar are mounted) the value of the "post_ID" hidden input available in the classic editor
 * screen is used as a fallback.
 *
 * @return {number|null} The post ID or null if the post ID is not available.
 */
function getPostId() {
  const editorStore = wp.data.select('core/editor');
  if (editorStore && typeof editorStore.getCurrentPostId === 'function') {
    const postId = editorStore.getCurrentPostId();
    if (postId) {
      return parseInt(postId, 10);
    }
  }
  const postIdInput = document.getElementById('post_ID');
  if (postIdInput && postIdInput.value) {
    return parseInt(postIdInput.value, 10);
  }
  return null;
}

/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

module.exports = window["React"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _automatic_links_options_index_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./automatic-links-options/index.js */ "./src/automatic-links-options/index.js");
/* harmony import */ var _interlinks_optimization_index__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./interlinks-optimization/index */ "./src/interlinks-optimization/index.js");
/**
 * All blocks related JavaScript files should be imported here.
 * You can create a new block folder in this directory and include code for that
 * block here as well.
 *
 * All blocks should be included here since this is the file that Webpack is
 * compiling as the entry point.
 */



})();

/******/ })()
;
//# sourceMappingURL=index.js.map