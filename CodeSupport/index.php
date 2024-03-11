<?php
$DisplayPage=(isset($_GET['Folder']))?$_GET['Folder']:'CSS';
$DisplayPage .= DIRECTORY_SEPARATOR;
require $DisplayPage.'ExterLink.php';
//將網址變數取出串成變數
$LinkVar='?';
foreach ($_GET as $key => $value) {
    $LinkVar.=($LinkVar=='?')?$key . '=' . $value:'&' . $key . '=' . $value;
}
?>

<?php
$CurrentAddress = getcwd();
// 過濾出資料夾
$folders = array_filter(scandir($CurrentAddress), function($item) use ($CurrentAddress) {
    return is_dir($CurrentAddress . DIRECTORY_SEPARATOR . $item) && $item != '.' && $item != '..' && $item != '.git';
});

$GoPage = (isset($_GET['Folder']) and isset($_GET['EditData']))?'EditDataPage.php':'ShowWordPage.php';
require 'Page/'.$GoPage;

?>

<script>
    
        //無記錄提交表單用
    function submitForm() {
        document.getElementById("myForm").submit();
    }
    <?php 
        if($_SERVER["REQUEST_METHOD"] == "POST"){echo 'window.location.replace(window.location.href);';}
    ?>
</script>

