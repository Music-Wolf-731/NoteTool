<?php
$DisplayPage=(isset($_GET['Folder']))?$_GET['Folder']:'English';
$DisplayPage .= DIRECTORY_SEPARATOR;
require $DisplayPage.'ExterLink.php';
//將網址變數取出串成變數
$LinkVar='?';
foreach ($_GET as $key => $value) {
    $LinkVar.=($LinkVar=='?')?$key . '=' . $value:'&' . $key . '=' . $value;
}




$CurrentAddress = getcwd();
// 過濾出資料夾
$folders = array_filter(scandir($CurrentAddress), function($item) use ($CurrentAddress) {
    return is_dir($CurrentAddress . DIRECTORY_SEPARATOR . $item) && $item != '.' && $item != '..' && $item != '.git';
});


?>



<?php

// 讀取JSON文件
$jsonData = file_get_contents($DisplayPage.'word.json');
// 解析JSON數據
$userData = json_decode($jsonData, true);

//在json陣列中，找到name項目符合目標的項目
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $OnSet = false;
    //如果是已經有的內容，就更新目前的項目
    foreach ($userData as $key => $value) {
        if($value['name']==$_POST["keyword"]){
            $OnSet=true;
            if(isset($_POST['Delete'])){unset($userData[$key]);continue;}
            $userData[$key]['mean']=$_POST['mean'];
            $userData[$key]['describe']=$_POST['describe'];
        }
    }
    //如果是新的內容，就新增在列表最後
    if(!$OnSet){
        $Set['name']=$_POST['keyword'];
        $Set['mean']=$_POST['mean'];
        $Set['describe']=$_POST['describe'];
        $userData[]=$Set;
    }
}


// 使用 usort 函數進行排序
usort($userData, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});


// $userData->orders[] = $newOrder;

// 轉換回JSON字符串
$jsonString = json_encode($userData, JSON_PRETTY_PRINT);

// 保存回文件
file_put_contents($DisplayPage.'word.json', $jsonString);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function ChangePage(value){
            window.location.href = './?Folder=' + value;
        }
    </script>
</head>
<body>




    <?php
    
        $PageOption = '';
        foreach ($folders as $key => $value) {
            $PageOption .= '<option>'.$value.'</option>';
        }
        $PageOption = 
        '<div><h3>'.$DisplayPage.'</h3><select id="countries" onchange="ChangePage(this.value)" name="country">
            <option>換頁</option>'.
            $PageOption
        .'</select></div>';



        echo '<div id="TopBar">'.$PageOption.'<a href=./'.$LinkVar.'><p>重整</p></a></div>';





        //寫入文字流視框
        $ForPrint='';
        foreach ($userData as $key => $value) {
            
            $SingleWord=(mb_strlen($value["name"])<2)?" SinBox":"";

            $writeBox='';
            $writeBox = '
            <div class="WordBox'.$SingleWord.'">
                <p class="word">'.$value["name"].'</p>
                <p class="mean">'.$value["mean"] .'</p>
                <p class="inner">'.$value["describe"] .'</p>
                <div class="FlowBox">
                </div>
            </div>';
            $ForPrint .= $writeBox;
        }
        echo '<div id="WordWindow"><div id="WordBoxList">'.$ForPrint.'</div></div>';



    ?>



    <div class="describe">
        <div class="page">
            <div class="clickable desc"><div class="ChangePageUse"></div><p>描述</p></div>
            <div class="clickable edit"><div class="ChangePageUse"></div><p>修改</p></div>
        </div>
        <div class="display"> 
            <div style="display: block;" class="desc"><p></p></div>
            <div style="display: none;" class="edit">
                <form  action="./<?php echo $LinkVar ?>" method="post">
                    <div>
                        <label for="keyword">單字：</label><input class="InputText" id="keyword" name="keyword" type="text" autocomplete="off">
                        <label for="mean">描述：</label><input class="InputText" id="mean" name="mean" type="text" autocomplete="off">
                    </div>
                    <div style="display:flex;height:45%;">
                        <textarea id="describe" name="describe"></textarea>
                        <div style="display:flex;flex-wrap: wrap;justify-content: flex-end;align-items: center;">
                            <label for="Delete">刪除：</label><input id="Delete" name="Delete" type="checkbox">
                            <input type="submit" value="Submit">
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="linkBox">
            <?php
                foreach ($ExterLink as $key => $value) {
                    echo '
                        <a class="ExterLink_'.$key.'" href="./">
                            <img src="'.$value['img'].'">
                        </a>
                    
                    ';
                }
            ?>
        </div>
    </div>


    <script>
        // 获取 WordBoxList 元素
        const wordBoxList = document.getElementById('WordBoxList');


        // 获取 describe 元素中的链接
        <?php
            foreach ($ExterLink as $key => $value) {
                echo 'const JS_ExterLink_'.$key.' = document.querySelector(".linkBox .ExterLink_'.$key.'");';
            }
        ?>
        const descriGLink = document.querySelector('.linkBox .google');
        const descriCLink = document.querySelector('.linkBox .Cambridge');

        // 获取 describe 元素中的链接
        const desc = document.querySelector('.display .desc p');

        // 监听 WordBoxList 内部每个 WordBox 的点击事件
        wordBoxList.addEventListener('click', function(event) {
        // 确保点击的是 WordBox 元素
            if (event.target.classList.contains('FlowBox')) {
                // 获取点击的 WordBox 元素内的文字
                const clickedWord = event.target.closest('.WordBox').querySelector('.word').textContent;
                const clickedmean = event.target.closest('.WordBox').querySelector('.mean').textContent;
                const clickedinner = event.target.closest('.WordBox').querySelector('.inner').textContent;

                <?php
                
                ?>
                // 将文字替换到 describe 区域的链接 href 属性中
                <?php
                    foreach ($ExterLink as $key => $value) {
                        echo 'JS_ExterLink_'.$key.'.href = `'.$value['link'].'`;';
                    }
                ?>
                
                // 将文字替换到 describe 区域的链接 href 属性中
                desc.textContent = clickedinner;
                document.getElementById('keyword').value = clickedWord;
                document.getElementById('mean').value = clickedmean;
                document.getElementById('describe').value = clickedinner;

                // 阻止默认行为，防止链接被点击时跳转
                event.preventDefault();
            }
        });




        // 监听 WordBoxList 内部每个 WordBox 的点击事件
        wordBoxList.addEventListener('click', function(event) {
            // 确保点击的是 WordBox 元素
            if (event.target.classList.contains('page')) {
                // 获取点击的 WordBox 元素内的文字
                const clickedWord = event.target.closest('.describe').querySelector('.word').textContent;


                // 将文字替换到 describe 区域的链接 href 属性中
                describeLink.href = `https://translate.google.com.tw/?sl=en&tl=zh-TW&text=${clickedWord}`;

                // 阻止默认行为，防止链接被点击时跳转
                event.preventDefault();
            }
        });

        
        // 获取父级容器
        const describe = document.querySelector('.describe');

        // 监听点击事件
        describe.addEventListener('click', function (event) {
            // 判断点击的元素是否包含 clickable 类
            if (event.target.classList.contains('ChangePageUse')) {
            // 获取点击元素的类名
            const clickedClass = event.target.closest('.clickable').classList[1];

            // 隐藏所有的 desc 和 edit
            document.querySelectorAll('.display>div').forEach(element => {
                element.style.display = 'none';
            });

            // 显示对应的 desc 或 edit
            document.querySelector(`.display .${clickedClass}`).style.display = 'block';
            }
        });
        
    </script>
</body>
</html>