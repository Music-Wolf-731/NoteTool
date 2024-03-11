

<?php
// 讀取JSON文件 並 解析JSON數據
$JsonExtarData = json_decode( file_get_contents($DisplayPage.'data.json') , true);


// 讀取JSON文件
$jsonData = file_get_contents($DisplayPage.'ItemList.json');
// 解析JSON數據
$userData = json_decode($jsonData, true);

//在json陣列中，找到name項目符合目標的項目
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    if(!isset($_POST['Top'])){$_POST['Top']=false;}
    $OnSet = false;
    //如果是已經有的內容，就更新目前的項目
    foreach ($userData as $key => $value) {
        if($value['name']==$_POST["keyword"]){
            $OnSet=true;
            if(isset($_POST['Delete'])){unset($userData[$key]);continue;}
            $userData[$key]['mean']=$_POST['mean'];
            $userData[$key]['describe']=$_POST['describe'];
            $userData[$key]['Top']=($_POST['Top']=='on')?true:false;
            $userData[$key]['Data'] =(isset($_POST['Data']))? $_POST['Data']:[];
        }
    }
    //如果是新的內容，就新增在列表最後
    if(!$OnSet){
        $Set['name']=$_POST['keyword'];
        $Set['mean']=$_POST['mean'];
        $Set['describe']=$_POST['describe'];
        $Set['Top']=($_POST['Top']=='on')?true:false;
        $userData[]=$Set;
    }
}
foreach ($userData as $key => $value) {
    if(isset($value['Star'])){unset($userData[$key]['Star']);}
}


// 使用 usort 函數進行排序
usort($userData, function($a, $b) {
    return strcmp($a['name'], $b['name']) ;
});


// $userData->orders[] = $newOrder;

// 轉換回JSON字符串
$jsonString = json_encode($userData, JSON_PRETTY_PRINT);

// 保存回文件
file_put_contents($DisplayPage.'ItemList.json', $jsonString);
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



        echo '<div id="TopBar">'.$PageOption.'<p><a href=./'.$LinkVar.'>重整</a> / <a href=./'.$LinkVar.'&EditData=00>編輯資料集</a></p></div>';





        //寫入文字流視框
        $ForPrint='';$typeLine=true;$GroupOrderKey=['置頂'];
        foreach ($JsonExtarData as $key => $value_L) {
            if($value_L['Name']!=='Group'){continue;}
            $GroupData=$value_L;
            foreach ($value_L['Data'] as $key => $value) {
                $GroupOrderKey[]= array_search($value, $value_L['Id']);
            }
        }
        $GroupOrderKey[]='未分類';
        foreach ($GroupOrderKey as $key => $GroupId) {
            if($GroupId !== '置頂'){
                $TitleWrite=($GroupId == '未分類')?'未分類':$GroupData['Id'][$GroupId];
                $TitleWrite='<div class="ExtarDataTitle"><p>'.$TitleWrite.'</p><div></div></div>';
                $ForPrint .= $TitleWrite;
            }
            foreach ($userData as $key => $value) {
                if($GroupId == "置頂"){
                    if(!isset($value['Top']) or !$value['Top']){continue;};
                } else if($GroupId !== "未分類"){
                    if(!isset($value['Data']['Group'])){continue;};
                    if(!in_array($GroupId, $value['Data']['Group'])){continue;};
                } else {
                    if(isset($value['Data']['Group']) and !empty($value['Data']['Group'])){continue;};
                }

                
                //預設class
                $ExtraClass=' Default';
                //重要class
                $ExtraClass=(isset($value['Top']) and $value['Top'])?" KeyWord":$ExtraClass;


                $TopType=(isset($value['Top']) and $value['Top'])?"on":"off";                

                $DataWrite = (isset($value["Data"]))? json_encode($value["Data"]) :'[]' ;
                $writeBox = '
                <div class="WordBox'.$ExtraClass.'">
                    <p class="word">'.$value["name"].'</p>
                    <p class="mean">'.$value["mean"] .'</p>
                    <p class="inner">'.$value["describe"].'</p>
                    <p class="TopType">'.$TopType.'</p>
                    <p class="Data">'.$DataWrite .'</p>
                    <div class="FlowBox">
                    </div>
                </div>';
                $ForPrint .= $writeBox;
            }

        }

        echo '<div id="WordWindow"><div id="WordBoxList">'.$ForPrint.'</div></div>';



    ?>



    <div class="describe">
        <div class="page">
            <div class="clickable desc"><div class="ChangePageUse"></div><p>描述</p></div>
            <div class="clickable edit"><div class="ChangePageUse"></div><p>修改</p></div>
            <div class="clickable Data"><div class="ChangePageUse"></div><p>類別</p></div>
        </div>
        <form id="myForm"  action="./<?php echo $LinkVar ?>" method="post">
            <div class="display"> 
                <div style="display: block;" class="desc"><p></p></div>
                <div style="display: none;" class="edit">
                    
                        <div>
                            <input id="reast" type="reset" value="清空">
                            <div>
                                <span><label for="keyword">單字：</label><input class="InputText" id="keyword" name="keyword" type="text" autocomplete="off"></span>
                                <span><label for="mean">描述：</label><input class="InputText" id="mean" name="mean" type="text" autocomplete="off"></span>
                            </div>
                        </div>
                        <textarea id="describe" name="describe"></textarea>
                        <div class="component">
                            <div>
                                <span><label for="Top">置頂</label><input id="Top" name="Top" type="checkbox"></span>
                                <input type="button" onclick="submitForm()" value="送出">
                            </div>
                            <span><label for="Delete">刪除：</label><input id="Delete" name="Delete" type="checkbox"></span>
                        </div>
                    
                </div>
                <div style="display: none;" class="Data">
                    
                    <?php
                        $OtherData = '';
                        foreach ($JsonExtarData as $key => $value_L) {
                            $OtherData .='<div class="ExtarDataTitle"><p>' . $value_L['Name'].'</p><div></div></div>';
                            $Option = '';
                            foreach ($value_L['Id'] as $key => $value) {
                                $Option .='
                                    <input type="checkbox" id="ExtarData_'.$value_L['Name'].$key.'" name="Data['.$value_L['Name'].'][]" value="'.$key.'">
                                    <label for="ExtarData_'.$value_L['Name'].$key.'">'.$value.'</label>
                                ';
                            }
                            $OtherData .= '<div class="ExtarDataOption">'.$Option.'</div>';

                        }
                        echo $OtherData;
                    ?>
                    
                    <div style="width: 100%;height: 30px;display: flex;align-items: center;justify-content: space-around;">
                        <input type="button" onclick="submitForm()" value="送出">
                    </div>
                </div>
            </div>
        </form>
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
        function ChangeDataCheckBox(JsonData) {
            ////這個是抓入Data的javascript
            // JSON 字符串
            let jsonString = JsonData;

            // // 解析 JSON 字符串为 JavaScript 对象
            let DataArray = Object.entries(JSON.parse(jsonString));

            document.querySelectorAll('.ExtarDataOption input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });

            DataArray.forEach(value_L => {
                value_L[1].forEach(value =>{
                    console.log(value_L[0]+value);
                    // document.querySelectorAll('.ExtarDataOption .ExtarData_GroupID002');
                    // document.querySelectorAll('.ExtarDataOption .ExtarData_' + value_L[0]+value).checked = true;
                    document.querySelectorAll('.ExtarDataOption #ExtarData_'+ value_L[0]+value).forEach(checkbox => {
                        checkbox.checked = true;
                    });
                });
            });
            
        }



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
                const clickeData = event.target.closest('.WordBox').querySelector('.Data').textContent;
                const clickeTop = event.target.closest('.WordBox').querySelector('.TopType').textContent;
                console.log(clickeTop);
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

                const CheckBox = document.getElementById('Top');
                CheckBox.checked = (clickeTop=='on')? true : false ;
                ChangeDataCheckBox(clickeData);

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