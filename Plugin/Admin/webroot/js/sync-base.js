/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.4.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.SyncBase = {

    activeAjaxRequests : [],

    resetForm : function () {
        this.resetButton($('.sync-button-wrapper a'), 'fa-refresh');
    },

    resetButton : function (button, initialClass) {
        foodcoopshop.Helper.enableButton(button);
        foodcoopshop.Helper.removeSpinnerFromButton(button, initialClass);
    },

    getLoginForms : function () {
        return $('form.sync-login-form');
    },

    hideSyncForm : function () {
        $('form.sync-login-form').hide();
        $('.sync-button-container').hide();
    },

    showSyncForm : function () {
        $('form.sync-login-form').show();
        $('.sync-button-container').show();
    },

    reformatProductListRows : function (productListRows) {

        var oddClass = 'custom-odd';
        var i = 0;

        productListRows.each(function () {
            $(this).removeClass(oddClass);
            if ($(this).css('display') === 'table-row') {
                if (i % 2 === 1) {
                    $(this).addClass(oddClass);
                    // do not forget product attributes
                    $(this).nextUntil('.main-product').addClass(oddClass);
                }
                i++;
            }
        });
    },

    /**
     * @return stored credentials count
     */
    loadCredentialsFromLocalStorage : function () {

        foodcoopshop.Helper.bindToggleLinks(true);
        var credentials = localStorage.getItem('credentials');
        if (!credentials) {
            return 0;
        }

        var storedCredentials = $.parseJSON(credentials);
        for (var credential of storedCredentials) {
            var loginForm = $("form.sync-login-form[data-sync-domain='" + credential.domain +"']");
            loginForm.find('input.username').val(credential.username);
            loginForm.find('input.password').val(credential.password);
            loginForm.find('.toggle-link').trigger('click');
        }
        return storedCredentials.length;

    },

    /**
     * @param data: needs to be associative array with syncDomain as key, null is allowed
     */
    doApiCall : function (url, data, callback) {

        var loginForms = foodcoopshop.SyncBase.getLoginForms();
        var dataRequested = false;
        foodcoopshop.SyncBase.activeAjaxRequests = [];
        loginForms.removeClass('error');

        loginForms.each(function () {
            var username = $(this).find('.username').val().trim();
            var password = $(this).find('.password').val().trim();
            if (username != '' && password != '') {
                dataRequested = true;
                var syncDomain = $(this).data('sync-domain');
                if (data !== null && data[syncDomain]) {
                    var postData = {
                        data: {
                            data: data[syncDomain],
                            baseDomain: data[syncDomain]['baseDomain']
                        }
                    };
                } else {
                    if (data !== null) {
                        console.log(data);
                        console.log('wrong format for data: needs syncDomain as key!');
                        return false;
                    }
                }
                var request = foodcoopshop.SyncBase.baseAuthAjaxCall(syncDomain + url, postData, callback, username, password);
                foodcoopshop.SyncBase.activeAjaxRequests.push(request);
            }

        });

        if (foodcoopshop.SyncBase.activeAjaxRequests.length > 0) {
            $.when.apply($, foodcoopshop.SyncBase.activeAjaxRequests)

              .done(function () {

                 foodcoopshop.Helper.removeFlashMessage();

                 // "arguments" have different data strucure when only one login form is available or used
                 var oneRequest = $.type(arguments[1]) === 'string';
                 var responseObjects = [];

                if (!oneRequest) {
                    $.each(arguments, function (index, response) {
                         responseObjects.push(response);
                    });
                } else {
                    responseObjects = [arguments];
                }

                var credentials = [];
                for (var response of responseObjects) {
                    // if there was a callback passed, call it
                    if (response[2] && response[2].callback) {
                        response[2].callback(response[0]);
                    }
                    // no associative array allowed here!
                    var domain = response[2].responseJSON.app.domain;
                    credentials.push({domain: domain, username: response[2].username, password: response[2].password});

                    // auto hide login form with valid credentials
                    var loginForm = $("form.sync-login-form[data-sync-domain='" + domain +"']");
                    if (loginForm.find('.toggle-content').css('display') == 'block') {
                        loginForm.find('.toggle-link').trigger('click');
                    }
                }
                // store credentials in localStorage
                localStorage.setItem('credentials', JSON.stringify(credentials));

              }).fail(function (response) {

                $(this).map(function () {
                    var syncDomain = foodcoopshop.SyncBase.getHostnameFromUrl($(this)[0].url);
                    var loginForm = $("form.sync-login-form[data-sync-domain='" + syncDomain +"']");
                    loginForm.addClass('error');
                    foodcoopshop.Helper.showOrAppendErrorMessage(syncDomain + ': ' + (response.responseJSON && response.responseJSON.message ? response.responseJSON.message : 'E-Mail-Adresse oder Passwort falsch.'));
                });

              }).always(function () {

                foodcoopshop.SyncBase.resetForm();

              });
        }

        if (!dataRequested) {
            foodcoopshop.SyncBase.resetForm();
        }

    },

    getHostnameFromUrl(url) {
        var a = document.createElement('a');
        a.href = url;
        return a.protocol + '//' + a.hostname;
    },

    baseAuthAjaxCall : function (url, data, callback, username, password) {
        return $.ajax({
            type: 'POST',
            url: url,
            data: data,
            crossDomain: true,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.callback = callback;
                xhr.username = username;
                xhr.password = password;
                xhr.setRequestHeader('Authorization', 'Basic ' + btoa(username + ':' + password));
            }
        });
    }

}
