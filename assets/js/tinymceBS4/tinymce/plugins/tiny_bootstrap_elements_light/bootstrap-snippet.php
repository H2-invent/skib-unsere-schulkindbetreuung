<?php @session_start();
$css_paths = explode(",", addslashes($_GET['css_paths']));
$allowEdit = addslashes($_GET['allowEdit']);
function siteURL()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];

    return $protocol.$domainName;
}
define('SITE_URL', siteURL());

/* language */

if (file_exists('langs/' . $_GET['language'] . '.php')) {
    $lang = $_GET['language'];
} else { // default
    $lang = 'en_EN';
}
require_once 'langs/' . $lang . '.php';
require_once 'snippets/Snippets.php';
$snippets = new Snippets('snippets/snippets.xml', $allowEdit);
$snippets->getSnippets();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php
    foreach ($css_paths as $css_path) {
        echo '<link rel="stylesheet" href="' . trim($css_path) . '">' . " \n";
    }
    ?>
    <link rel="stylesheet" href="css/plugin.min.css">
    <link href="google-code-prettify/prettify.css" type="text/css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Source+Code+Pro' rel='stylesheet' type='text/css'>
</head>
<body>
    <div class="container">
        <div id="ajax-msg"></div>
        <div class="row margin-bottom-md">
            <div class="choice-title">
                <span><?php echo SNIPPETS; ?></span>
            </div>
            <div id="snippets-list" class="col-xs-12">
                <?php echo $snippets->render(); ?>
            </div>
        </div>
<?php
if ($allowEdit == 'true') { ?>
            <div class="row margin-bottom-md" id="new-snippet-form-wrapper">
                <div class="choice-title">
                    <span><?php echo ADD_NEW_SNIPPET; ?></span>
                </div>
                <div class="col-sm-8 col-xs-6 col-sm-offset-4 col-xs-offset-6">
                	<div id="end-edit-warning"></div>
                	<a href="http://codecanyon.net/item/tiny-bootstrap-elements-wordpress-plugin/10293837"><p class="button button-primary button-large" style="height:auto !important;padding:20px !important"><strong>Get this feature on PRO version at</strong><br>http://codecanyon.net/item/tiny-bootstrap-elements-wordpress-plugin/10293837</p></a>
                    <button type="submit" class="btn btn-primary" id="new-snippet-ok-button"><?php echo OK; ?></button>
                </div>
            </div>
    <?php
} // end $allowEdit ?>
        <div class="row" id="preview">
            <div id="preview-title" class="margin-bottom-md">
                <span class="label-primary"><?php echo PREVIEW; ?></span>
            </div>
            <div class="col-sm-12 margin-bottom-md text-center test-snippet-wrapper" id="test-wrapper">
            </div>
        </div>
        <div class="row">
            <div id="code-title">
                <a href="#" id="code-slide-link"><?php echo CODE; ?> <i class="glyphicon glyphicon-arrow-up"></i></a>
<?php
if ($allowEdit == 'true') { ?>
                <a href="#" id="edit-snippet-button" class="btn btn-default" data-toggle="tooltip" title="" data-original-title="<?php echo CHOOSE_SNIPPET_TO_EDIT; ?>">
                    <?php echo EDIT; ?> <i class="glyphicon glyphicon-edit"></i>
                </a>
                <a href="#" id="delete-snippet-button" class="btn btn-default" data-toggle="tooltip" title="" data-original-title="<?php echo CHOOSE_SNIPPET_TO_DELETE; ?>">
                    <?php echo DELETE_CONST; ?> <i class="glyphicon glyphicon-remove"></i>
                </a>
    <?php
} // end $allowEdit ?>
            </div>
            <div class="col-sm-12" id="code-wrapper">
                <pre></pre>
            </div>
        </div>
    </div>
<script type="text/javascript">
    //  Get Parent jQuery Variable
    var args = top.tinymce.activeEditor.windowManager.getParams();
    var $ = args['jquery'];
    var jQuery = args['jquery'];

    //  Get Current Context for jQuery
    var context = document.getElementsByTagName("body")[0];
</script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/utils.min.js"></script>
<script type="text/javascript" src="js/jquery.htmlClean.min.js"></script>
<script type="text/javascript" src="google-code-prettify/prettify.js"></script>
<script type="text/javascript">
/* jshint strict: true */
/*global $, makeResponsive, getBootstrapStyles, updateCode*/

var snippetIndex,
    snippetCode;

var initClickEvents,
    slideCodeWrapperDown;

$(document).ready(function () {
    'use strict';
    makeResponsive();
    getBootstrapStyles();

<?php
if ($allowEdit == 'true') { ?>
    var disableOkButton,
        showAddSnippetForm;

    disableOkButton = function (event) {
        event.preventDefault();
        var target = $('#code-wrapper', context);
        if ($('#new-snippet-form-wrapper', context).css('display') == 'block') {
            target = $('#end-edit-warning', context);
        }
        target.prepend('<div class="alert alert-warning alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>Please end edit before validate</div>');

        return false;
    };

    slideCodeWrapperDown = function () {
        if ($('#code-wrapper', context).css('display') == 'none') {
            $('#code-slide-link', context).trigger('click');
        }
    };

    /* Add snippet */

    showAddSnippetForm = function () {
        $('#new-snippet-form-wrapper', context).show(400).scrollToTop();

        /* Disable main OK button while adding snippet */

        $(window.parent.document.body).find('.mce-panel.mce-foot').find('.mce-widget.mce-abs-layout-item.mce-first').find('button').addClass('disabled').on('click', disableOkButton);
        $('#new-snippet-ok-button', context).on('click', function () {
            $('#new-snippet-form-wrapper', context).hide(400);

            /* Re-enable OK button */

            $(window.parent.document.body).find('.mce-panel.mce-foot').find('.mce-widget.mce-abs-layout-item.mce-first').find('button').removeClass('disabled').off('click');
        });
    };

    /* new snippet form */

    $('#new-snippet-form-wrapper', context).hide();

    /* Edit snippet */

    $('#edit-snippet-button', context).tooltip();
    $('#edit-snippet-button', context).on('click', function () {
        $('#code-title', context).scrollToTop();
        if ($(this).hasClass('btn-primary')) {
            slideCodeWrapperDown();
            var editSnippetIndex = $('.select-snippet.active', context).attr('data-index');
            $('#code-wrapper', context).html('<a href="http://codecanyon.net/item/tiny-bootstrap-elements-wordpress-plugin/10293837"><p class="button button-primary button-large" style="height:auto !important;padding:20px !important"><strong>Get this feature on PRO version at</strong><br>http://codecanyon.net/item/tiny-bootstrap-elements-wordpress-plugin/10293837</p></a><button type="submit" class="btn btn-primary" id="edit-snippet-ok-button">OK</button>');

            /* Disable main OK button while editing snippet */

            $(window.parent.document.body).find('.mce-panel.mce-foot').find('.mce-widget.mce-abs-layout-item.mce-first').find('button').addClass('disabled').on('click', disableOkButton);

            /* Update Snippet (Ajax) */

            $('#edit-snippet-ok-button', context).on('click', function (e) {
                e.preventDefault();
                $('.selector.select-snippet[data-index=' + editSnippetIndex + ']', context).trigger('click');

            	/* Re-enable OK button */

                $(window.parent.document.body).find('.mce-panel.mce-foot').find('.mce-widget.mce-abs-layout-item.mce-first').find('button').removeClass('disabled').off('click');
            });

        }
    });

    /* Delete snippet */

    $('#delete-snippet-button', context).tooltip();
    $('#delete-snippet-button', context).on('click', function () {
        if ($(this).hasClass('btn-danger')) {
            slideCodeWrapperDown();
            $('#code-title', context).scrollToTop();
            var deleteSnippetIndex = $('.select-snippet.active', context).attr('data-index');
            $('#code-wrapper', context).html('<a href="http://codecanyon.net/item/tiny-bootstrap-elements-wordpress-plugin/10293837"><p class="button button-primary button-large" style="height:auto !important;padding:20px !important"><strong>Get this feature on PRO version at</strong><br>http://codecanyon.net/item/tiny-bootstrap-elements-wordpress-plugin/10293837</p></a><button type="submit" class="btn btn-primary" id="delete-snippet-ok-button">OK</button>');

            /* Disable main OK button while editing snippet */

            $(window.parent.document.body).find('.mce-panel.mce-foot').find('.mce-widget.mce-abs-layout-item.mce-first').find('button').addClass('disabled').on('click', disableOkButton);

            /* Update Snippet (Ajax) */

            $('#delete-snippet-ok-button', context).on('click', function (e) {
                e.preventDefault();
                $('.selector.select-snippet[data-index=' + deleteSnippetIndex + ']', context).trigger('click');

            	/* Re-enable OK button */

                $(window.parent.document.body).find('.mce-panel.mce-foot').find('.mce-widget.mce-abs-layout-item.mce-first').find('button').removeClass('disabled').off('click');
            });
        }
    });
    <?php
} // end $allowEdit ?>

    /* select snippet */

    initClickEvents = function () {
        $('.selector.select-snippet', context).each(function (event, element) {
            $(element).on('click', function (event) {
                $('.selector.select-snippet', context).removeClass('active');
                $(this).addClass('active');
                snippetIndex = $(this).attr('data-index');
                snippetCode = $('#content-' + snippetIndex, context).html();
                $('#test-wrapper', context).fadeOut(200, function () {
                    $(this).html(snippetCode);
                    updateCode();
                    $(this).fadeIn(200);
                });
                $('#edit-snippet-button', context).removeClass('btn-default').addClass('btn-primary').tooltip('destroy');
                $('#delete-snippet-button', context).removeClass('btn-default').addClass('btn-danger').tooltip('destroy');
            });
        });
<?php
if ($allowEdit == 'true') { ?>
        $('#add-new-snippet-btn', context).on('click', showAddSnippetForm);
    <?php
} // end $allowEdit ?>
    };
    initClickEvents();
});
</script>
</body>
</html>
