/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
foodcoopshop.SyncBase = {

    activeAjaxRequests : [],

    // versions are dynamically set in view
    versionFoodCoopShop: '',
    versionNetworkPlugin: '',

    init : function () {
        foodcoopshop.Helper.showContent();
        foodcoopshop.Helper.initMenu();
        foodcoopshop.Helper.initLogoutButton();
        foodcoopshop.Admin.setMenuFixed();
        foodcoopshop.Admin.adaptContentMargin();
    },

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

    reformatProductListRows : function (productList, useSubRowWithoutMainRowMode) {

        var oddClass = 'custom-odd';
        var i = 0;

        productList.find('tr').removeClass(oddClass);

        productList.find('tr.main-product').each(function () {
            if ($(this).css('display') === 'table-row') {
                if (i % 2 === 1) {
                    $(this).addClass(oddClass);
                    $(this).nextUntil('.main-product').addClass(oddClass);
                }
                i++;
            } else {
                if (useSubRowWithoutMainRowMode) {
                    var subRows = $(this).nextUntil('.main-product', '.sub-row');
                    if (i % 2 === 1 && subRows.length > 1) {
                        $(this).nextUntil('.main-product').addClass(oddClass);
                        i++;
                    }
                }
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
            var loginForm = $('form.sync-login-form[data-sync-domain=\'' + credential.domain +'\']');
            loginForm.find('input.username').val(credential.username);
            loginForm.find('input.password').val(credential.password);
            loginForm.find('.toggle-link').trigger('click');
        }
        return storedCredentials.length;

    },

    getNonEmptyLoginForms : function (loginForms) {
        var nonEmptyLoginForms = [];
        loginForms.each(function () {
            var username = $(this).find('.username').val().trim();
            var password = $(this).find('.password').val().trim();
            if (username != '' && password != '') {
                nonEmptyLoginForms.push($(this));
            }
        });
        return $(nonEmptyLoginForms);
    },

    /**
     * @param data: needs to be associative array with syncDomain as key, null is allowed
     */
    doApiCall : function (url, type, data, callback) {

        var loginForms = foodcoopshop.SyncBase.getLoginForms();
        var dataRequested = false;
        foodcoopshop.SyncBase.activeAjaxRequests = [];
        loginForms.removeClass('error');

        var nonEmptyLoginForms = this.getNonEmptyLoginForms(loginForms);
        nonEmptyLoginForms.each(function () {

            dataRequested = true;
            var syncDomain = $(this).data('sync-domain');
            var username = $(this).find('.username').val().trim();
            var password = $(this).find('.password').val().trim();
            var sendRequest = false;

            if (type == 'POST' && data !== null && data[syncDomain]) {
                var postData = {
                    data: {
                        data: data[syncDomain],
                        metaData : {
                            baseDomain: foodcoopshop.SyncBase.getHostnameFromUrl()
                        }
                    }
                };
                sendRequest = true;
            } else {
                if (data !== null) {
                    console.log('no data to post for ' + syncDomain);
                }
            }

            if (type == 'GET') {
                postData = data;
                sendRequest = true;
            }

            if (sendRequest) {
                var request = foodcoopshop.SyncBase.baseAuthAjaxCall(syncDomain + url, type, postData, callback, username, password);
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
                        var loginForm = $('form.sync-login-form[data-sync-domain=\'' + domain +'\']');
                        if (loginForm.find('.toggle-content').css('display') == 'block') {
                            loginForm.find('.toggle-link').trigger('click');
                        }
                    }
                    // store credentials in localStorage
                    localStorage.setItem('credentials', JSON.stringify(credentials));

                }).fail(function (response) {

                    $(this).map(function () {
                        var syncDomain = foodcoopshop.SyncBase.getHostnameFromUrl($(this)[0].url);
                        var loginForm = $('form.sync-login-form[data-sync-domain=\'' + syncDomain +'\']');
                        loginForm.addClass('error');
                        foodcoopshop.Helper.showOrAppendErrorMessage(syncDomain + ': ' + (response.responseJSON && response.responseJSON.message ? response.responseJSON.message : 'E-Mail-Adresse oder Passwort falsch.'));
                        $('.ui-dialog-content').dialog('close');
                        $('.ui-dialog .ajax-loader').hide();
                        $('.ui-dialog button').attr('disabled', false);
                        // TODO better implementation needed - maybe add callback for failures?
                        $('.product-list').removeClass('loader');
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

    baseAuthAjaxCall : function (url, type, data, callback, username, password) {

        if (!(type == 'GET' || type == 'POST')) {
            return false;
        }

        return $.ajax({
            type: type,
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

};
