'use strict'

const _ = require('underscore')

const TextUtils = require('%PathToCoreWebclientModule%/js/utils/Text.js'),
  App = require('%PathToCoreWebclientModule%/js/App.js')

module.exports = function (oAppData) {

  return {
    /**
     * Runs before application start. Subscribes to the event before post displaying.
     *
     * @param {Object} ModulesManager
     */
    start: function (ModulesManager) {

      if (App.getUserRole() === Enums.UserRole.Anonymous) {
        var onAfterlLoginFormConstructView = function (oParams) {
          var oLoginScreenView = oParams.View
          if (oLoginScreenView) {
            // Do not completely replace previous onSystemLoginResponse, because it might be already changed by another plugin
            var fOldOnSystemLoginResponse = oLoginScreenView.onSystemLoginResponse.bind(oLoginScreenView)
            if (!_.isFunction(fOldOnSystemLoginResponse)) {
              fOldOnSystemLoginResponse = oLoginScreenView.onSystemLoginResponseBase.bind(oLoginScreenView)
            }
            if (!_.isFunction(fOldOnSystemLoginResponse)) {
              fOldOnSystemLoginResponse = function () {}
            }
            oLoginScreenView.onSystemLoginResponse = function (oResponse, oRequest) {
              if (oResponse.Result && oResponse.Result['DuoUri'] != undefined) {
                location.replace(oResponse.Result['DuoUri']);
              } else {
                fOldOnSystemLoginResponse(oResponse, oRequest);
              }
            }
          }
        }.bind(this)
        App.subscribeEvent('StandardLoginFormWebclient::ConstructView::after', onAfterlLoginFormConstructView)
        App.subscribeEvent('MailLoginFormWebclient::ConstructView::after', onAfterlLoginFormConstructView)
      }
    },
  }
}
