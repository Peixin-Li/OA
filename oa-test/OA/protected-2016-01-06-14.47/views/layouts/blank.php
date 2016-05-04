<html>
    <head>
        <?php if(!empty($this->pageTitle)) : ?>
            <title><?php echo $this->pageTitle; ?></title>
        <?php endif; ?>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <?php 
            echo $content;
        ?>
    </body>
</html>

