<?php
echo "<script type='text/javascript'>";
echo "console.log('preview');";
echo "</script>";
?>

<!-- 用pdf显示office文档 -->
<html>
    <body marginwidth="0" marginheight="0" style="background-color: rgb(38,38,38)">
        <embed width="100%" height="100%" name="plugin" src="<?php echo $path; ?>" type="application/pdf">
    </body>
</html>
