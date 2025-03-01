/**
 *
 *  Fonctions utiles
 *
 */
function openPanel(name)
{
    $('.slide-panel-container[slide-panel="' + name + '"]').css({
        visibility: 'visible'
    }).promise().done(function () {
        $('.slide-panel-container[slide-panel="' + name + '"]').find('.slide-panel').animate({
            right: '0'
        })
    })
}

function closePanel()
{
    $('.slide-panel').animate({
        right: '-1000px',
    }).promise().done(function () {
        $('.slide-panel-container').css({
            visibility: 'hidden'
        })
    })
}

/**
 * Afficher un message d'alerte (success ou error)
 * @param {*} message
 * @param {*} type
 */
function printAlert(message, type = null, timeout = 2500)
{
    closeConfirmBox();
    $('#newalert').remove();

    if (type == null) {
        $('footer').append('<div id="newalert" class="alert"><div>' + message + '</div></div>');
    }
    if (type == "error") {
        $('footer').append('<div id="newalert" class="alert-error"><div>' + message + '</div></div>');
    }
    if (type == "success") {
        $('footer').append('<div id="newalert" class="alert-success"><div>' + message + '</div></div>');
    }

    if (timeout != 'none') {
        window.setTimeout(function () {
            $('#newalert').fadeTo(1500, 0).slideUp(1000, function () {
                $('#newalert').remove();
            });
        }, timeout);
    }
}

/**
 * Print a confirm alert box before executing specified function
 * @param {*} message
 * @param {*} myfunction1
 * @param {*} confirmBox1
 * @param {*} myfunction2
 * @param {*} confirmBox2
 */
function confirmBox(message, myfunction1, confirmBox1 = 'Delete', myfunction2 = null, confirmBox2 = null)
{
    /**
     *  First, delete all active confirm box if any
     */
    $("#newConfirmAlert").remove();

    /**
     *  Wait 50ms before creating new confirm box
     */
    setTimeout(function () {
        /**
         *  Case there is three choices
         */
        if (myfunction2 != null && confirmBox2 != null) {
            var $content = '<div id="newConfirmAlert" class="confirmAlert"><span></span><span>' + message + '</span><div class="confirmAlert-buttons-container"><span class="pointer btn-doConfirm1">' + confirmBox1 + '</span><span class="pointer btn-doConfirm2">' + confirmBox2 + '</span><span class="pointer btn-doCancel">Cancel</span></div></div>';
        /**
         *  Case there is two choices
         */
        } else {
            var $content = '<div id="newConfirmAlert" class="confirmAlert"><span></span><span>' + message + '</span><div class="confirmAlert-buttons-container"><span class="pointer btn-doConfirm1">' + confirmBox1 + '</span><span class="pointer btn-doCancel">Cancel</span></div></div>';
        }

        $('footer').append($content);

        /**
         *  If choice one is clicked
         */
        $('.btn-doConfirm1').click(function () {
            /**
             *  Execute function 1
             */
            myfunction1();

            /**
             *  Then remove alert
             */
            $("#newConfirmAlert").slideToggle(0, function () {
                $("#newConfirmAlert").remove();
            });
        });

        /**
         *  If choice two is clicked
         */
        $('.btn-doConfirm2').click(function () {
            /**
             *  Execute function 2
             */
            myfunction2();

            /**
             *  Then remove alert
             */
            $("#newConfirmAlert").slideToggle(0, function () {
                $("#newConfirmAlert").remove();
            });
        });

        /**
         *  If 'cancel' choice is clicked
         */
        $('.btn-doCancel').click(function () {
            /**
             *  Remove alert
             */
            $("#newConfirmAlert").slideToggle(0, function () {
                $("#newConfirmAlert").remove();
            });
        });
    }, 50);
}

function closeConfirmBox()
{
    $("#newConfirmAlert").slideToggle(50, function () {
        $("#newConfirmAlert").remove();
    });
}

function printLoading()
{
    $('#loading').remove();

    $('footer').append('<div id="loading"><p class="lowopacity">Loading</p><img src="/assets/images/loading.gif"></div>');
}

function hideLoading()
{
    setTimeout(function () {
        $('#loading').remove();
    },1500);
}

/**
 *  Slide div by class name or Id and save state in sessionStorage
 *  @param {*} name
 */
function slide(name)
{
    /**
     *  Get element display state (display: none or display: block/grid etc...)
     */
    var state = $(name).css('display');

    /**
     *  Open or close element
     */
    $(name).slideToggle('fast');

    /**
     *  If element was hidden (display: none) then it is now opened
     */
    if (state == 'none') {
        sessionStorage.setItem(name + '/opened', 'true');
    } else {
        /**
         *  Else it was opened and is now closed
         */
        sessionStorage.setItem(name + '/opened', 'false');
    }
}

/**
 * Rechargement du contenu d'un élément, par son Id
 * @param {string} id
 */
function reloadContentById(id)
{
    $('#' + id).load(location.href + ' #' + id + ' > *');
}

/**
 * Rechargement du contenu d'un élément par sa classe
 * @param {string} className
 */
function reloadContentByClass(className)
{
    $('.' + className).load(location.href + ' .' + className + ' > *');
}

/**
 *  Copie du contenu d'un élement dans le presse-papier
 *  @param {*} containerid
 */
function copyToClipboard(containerid)
{
    var range = document.createRange();
    range.selectNode(containerid);
    window.getSelection().removeAllRanges();
    window.getSelection().addRange(range);
    document.execCommand("copy");
    window.getSelection().removeAllRanges();

    printAlert('Copied to clipboard', 'success');
}

/**
 * Convert select tag to a select2 by specified id
 * @param {*} id
 */
function idToSelect2(id, placeholder = null, tags = false)
{
    if (placeholder == null) {
        placeholder = 'Select...';
    }

    $(id).select2({
        closeOnSelect: false,
        placeholder: placeholder,
        tags: tags,
        minimumResultsForSearch: Infinity /* disable search box */
    });
}

/**
 * Convert select tag to a select2 by specified class
 * @param {*} className
 */
function classToSelect2(className, placeholder = null, tags = false)
{
    if (placeholder == null) {
        placeholder = 'Select...';
    }

    $(className).select2({
        closeOnSelect: false,
        placeholder: placeholder,
        tags: tags,
        minimumResultsForSearch: Infinity, /* disable search box */
        allowClear: true /* add a clear button */
    });
}

 /**
  *  Print OS icon image
  */
function printOsIcon(os = '', os_family = '')
{
    if (os != '') {
        if (os.toLowerCase() == 'centos') {
            return '<img src="/assets/icons/products/centos.png" class="icon-np" title="' + os + '">';
        } else if (os.toLowerCase().match(/debian|armbian/i)) {
            return '<img src="/assets/icons/products/debian.png" class="icon-np" title="' + os + '">';
        } else if (os.toLowerCase().match(/ubuntu|kubuntu|xubuntu|mint/i)) {
            return '<img src="/assets/icons/products/ubuntu.png" class="icon-np" title="' + os + '">';
        }
    }

    /**
     *  If OS could not be found and OS family is specified
     */
    if (os_family != '') {
        if (os_family.toLowerCase().match(/debian|ubuntu|kubuntu|xubuntu|armbian|mint/i)) {
            return '<img src="/assets/icons/products/debian.png" class="icon-np" title="' + os + '">';
        } else if (os_family.toLowerCase().match(/rhel|centos|fedora/i)) {
            return '<img src="/assets/icons/products/redhat.png" class="icon-np" title="' + os + '">';
        }
    }

    /**
     *  Else return generic icon
     */
    return '<img src="/assets/icons/products/tux.png" class="icon-np" title="' + os + '">';
}

/**
 * Get cookie value by name
 * @param {*} cname
 * @returns
 */
function getCookie(cname)
{
    let name = cname + "=";
    let decodedCookie = decodeURIComponent(document.cookie);
    let ca = decodedCookie.split(';');

    for (let i = 0; i <ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }

    return "";
}

/**
 * Set cookie value
 * @param {*} cname
 * @param {*} cvalue
 * @param {*} exdays
 */
function setCookie(cname, cvalue, exdays)
{
    const d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    let expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/;Secure";
}

/**
 * Execute an ajax request
 * @param {*} controller
 * @param {*} action
 * @param {*} additionnalData
 * @param {*} reloadContainers
 */
function ajaxRequest(controller, action, additionnalData = null, reloadContainers = null)
{
    /**
     *  Default data
     */
    var data = {
        sourceUrl: window.location.href,
        sourceUri: window.location.pathname,
        controller: controller,
        action: action,
    };

    /**
     *  If additionnal data is specified, merge it with default data
     */
    if (additionnalData != null) {
        data = $.extend(data, additionnalData);
    }

    /**
     *  For debug only
     */
    // console.log(data);

    /**
     *  Ajax request
     */
    $.ajax({
        type: "POST",
        url: "/ajax/controller.php",
        data: data,
        dataType: "json",
        success: function (data, textStatus, jqXHR) {
            /**
             *  Retrieve and print success message
             */
            jsonValue = jQuery.parseJSON(jqXHR.responseText);
            printAlert(jsonValue.message, 'success');

            /**
             *  Reload containers if specified
             */
            if (reloadContainers != null) {
                for (let i = 0; i < reloadContainers.length; i++) {
                    reloadContainer(reloadContainers[i]);
                }
            }
        },
        error: function (jqXHR, textStatus, thrownError) {
            /**
             *  Retrieve and print error message
             */
            jsonValue = jQuery.parseJSON(jqXHR.responseText);
            printAlert(jsonValue.message, 'error');
        },
    });
}
