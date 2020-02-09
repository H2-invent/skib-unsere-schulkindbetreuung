<?php
$css_paths = explode(",", addslashes($_GET['css_paths']));
function siteURL()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];

    return $protocol.$domainName;
}
define('SITE_URL', siteURL());

/* language */

if (file_exists('langs/' . $_GET['language'] . '.php')) {
    require_once 'langs/' . $_GET['language'] . '.php';
} else { // default
    require_once 'langs/en_EN.php';
}
if (isset($_GET['edit'])) {
    $newBadge = false;
    $badgeText  = '';
    $badgeCode  = '';
} else {
    $newBadge = true;
    $badgeText  = '42';
    $badgeCode  = '<span class="badge">42</span>';
}
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
        <div class="row margin-bottom-md">
            <div class="choice-title">
                <span><?php echo BADGE_TEXT; ?></span>
            </div>
                <p></p>
                <div class="col-sm-6 col-sm-offset-3 form-inline">
                        <div class="form-group">
                        <label for="badge-text"><?php echo YOUR_TEXT; ?> : </label>
                            <input name="badge-text" class="form-control select-text" type="text" value="42">
                        </div>
                </div>
        </div>
        <div class="row" id="preview">
            <div id="preview-title" class="margin-bottom-md">
                <span class="label-primary"><?php echo PREVIEW; ?></span>
            </div>
            <div class="col-sm-12 text-center margin-bottom-md" id="test-wrapper">
                <?php echo $badgeCode ?>
            </div>
        </div>
        <div class="row">
            <div id="code-title">
                <a href="#"><?php echo CODE; ?> <i class="glyphicon glyphicon-arrow-down"></i></a>
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
    var newBadge  = '<?php echo $newBadge; ?>';
    var badgeText  = '<?php echo $badgeText; ?>';
    var badgeCode  = '<?php echo $badgeCode; ?>';
    $(document).ready(function () {

        $('#code-wrapper', context).hide();
        getBootstrapStyles();

        /* if newBadge === false, we get code from tinymce */

        if (!newBadge) {
            badgeCode = $(window.parent.document.body).find("iframe").contents().find(".badge.active").clone().wrap('<div>').parent().html();
            var find = new Array(/\s?data-mce-[a-z]+="[^"]+"/g, / active/);
            var replace = new Array('', '');

            for (var i = find.length - 1; i >= 0; i--) {
                badgeCode = badgeCode.replace(find[i], replace[i]);
            }
            $('#test-wrapper', context).html(badgeCode);
            badgeText = $('#test-wrapper .badge', context).html();

            /* input text value */

            $('.select-text', context).val(badgeText);
        }

        updateCode();

        /* text input */

        $('input[name="badge-text"]', context).on('click, focus', function () {
            $(this).on('keyup', function () {
                changeText(this);
            });
        });

        function changeText(input)
        {
            var value = $(input).prop('value');
            $('#test-wrapper', context).find('.badge').html(value);
            updateCode();
        }
    });
</script>
</body>
</html>
