<?php

    function DeleteItemListData($deleteID){
        $ItemListData = file_get_contents($GLOBALS['DisplayPage'].'ItemList.json');
        $ItemListData = json_decode($ItemListData, true);
        echo $deleteID .'<br>';
        foreach ($ItemListData as $key => $value) {
            if(isset($value['Data'][$_GET['EditData']]) ){
                if(in_array($deleteID,$value['Data'][$_GET['EditData']])){
                    unset($ItemListData[$key]['Data'][$_GET['EditData']][array_search($deleteID,$value['Data'][$_GET['EditData']])]);
                }
            }
            
        }
        // 轉換回JSON字符串
        $ItemListData = json_encode($ItemListData, JSON_PRETTY_PRINT);
        
        // 保存回文件
        file_put_contents($GLOBALS['DisplayPage'].'ItemList.json', $ItemListData);
    }
    //載入資料串
    
    $jsonData = file_get_contents($DisplayPage.'data.json');
    $jsonData = json_decode($jsonData, true);


    


    //取出網址內的變數並去除該頁核心變數
    $LinkVar="?";
    foreach ($_GET as $key => $value) {
        if($key=="EditData"){continue;}
        $LinkVar.=($LinkVar=='?')?$key.'='.$value:'&'.$key.'='.$value;
    }

    foreach ($jsonData as $key => $value) {
        if($value['Name'] == $_GET['EditData']){
            $DataKeyInJson = $key;$DataValueInJson = $value;
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST"){

        // $NewOrder處理
        // 使用explode將字符串分割成陣列
        // 使用array_map清除項目的頭尾空白
        //清除有重覆的項目
        $NewOrder = explode(",", $_POST['DataList']);
        $NewOrder = array_map('trim', $NewOrder);
        $NewOrder = array_unique($NewOrder);


        // 若陣列項目和原本無差異，即會開始進行修改
        if(
            empty(array_diff($DataValueInJson['Data'], $NewOrder)) and
            empty(array_diff($NewOrder, $DataValueInJson['Data']))
        ){
            $jsonData[$DataKeyInJson]['Data'] = $NewOrder;
        }


        //若有填入修改內容，同時又有指定修改項目則執行(包括新增)
        if(
            isset($_POST['DataForChange']) && 
            $_POST['DataChangeTo'] ){
            if(array_search(trim($_POST['DataChangeTo']),$DataValueInJson['Id'])){
                echo '更改內容和已有項目重覆，請重試';
            }else{
                $ChangeToVar = trim($_POST['DataChangeTo']);
                array_search($_POST['DataForChange'],$DataValueInJson['Data']);

                
                if($_POST['DataForChange']=='AddNewItem'){
                    //如果選擇的是「新增資料」會往這走
                    $i = 1;$bolin = false;
                    while (!$bolin){
                        if(!isset($DataValueInJson['Id']['ID'.sprintf("%03d", $i)])){
                            $jsonData[$DataKeyInJson]['Id']['ID'.sprintf("%03d", $i)] = $ChangeToVar;
                            $jsonData[$DataKeyInJson]['Data'][] = $ChangeToVar;
                            $bolin=true; 
                        } else {$i+=1;}
                    };
                    
                } else if($ChangeToVar == 'Delete'){
                    unset($jsonData[$DataKeyInJson]['Data'][array_search($_POST['DataForChange'],$DataValueInJson['Data'])]);
                    unset($jsonData[$DataKeyInJson]['Id'][array_search($_POST['DataForChange'],$DataValueInJson['Id'])]);
                    DeleteItemListData(array_search($_POST['DataForChange'],$DataValueInJson['Id']));
                } else {
                    
                    $jsonData[$DataKeyInJson]['Data'][array_search($_POST['DataForChange'],$DataValueInJson['Data'])] = $ChangeToVar;
                    $jsonData[$DataKeyInJson]['Id'][array_search($_POST['DataForChange'],$DataValueInJson['Id'])] = $ChangeToVar;
                }



            }
        }



    }

    
    // 轉換回JSON字符串
    $jsonData = json_encode($jsonData, JSON_PRETTY_PRINT);

    // 保存回文件
    file_put_contents($DisplayPage.'data.json', $jsonData);



    //重新讀取更新完的內容
    $jsonData = file_get_contents($DisplayPage.'data.json');
    $jsonData = json_decode($jsonData, true);


    
    //未指定類別時列出所有類別，有指定類別後取出類別資料
    $EditKey = false;$DataNameList=[];
    foreach ($jsonData as $key => $value) {
        $DataNameList[]= $value["Name"];
        $EditKey = ($value['Name'] == $_GET['EditData'])? $value["Data"] : $EditKey ;
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="EditDataStyles.css">
</head>
<body>

    
    <?php
    echo '<a href="'.$LinkVar.'"><p>取消編輯</p></a><a href="'.$LinkVar.'&EditData=Null00"><p>資料集列表</p></a>';
    //生成群組名按鈕
    if(!$EditKey){
        foreach ($DataNameList as $key => $value) {
            echo '<a href='.$LinkVar.'&EditData='.$value.'><p>'.$value.'</p></a>';
        }
    }
    
    else{
        $ChangeNameList = '';        
        foreach ($EditKey as $key => $value) {
            $EditStr = (isset($EditStr))?$EditStr.' , '.$value :$value;
            $ChangeNameList .= '<option value="'.$value.'">'.$value.'</option>';
        };
        $EditStr = '<textarea id="DataList" name="DataList">'.$EditStr.'</textarea>';

        $EditStr .= '
            
                <select id="DataForChange" name="DataForChange">
                    <option value="false" disabled selected>Choose Data</option>
                    '.$ChangeNameList.'
                    <option value="AddNewItem" style="color:red;">add New Item</option>
                </select>
                <label for="DataChangeTo">更改為</label><input id="DataChangeTo" name="DataChangeTo" placeholder="寫入要更正的值">
                <p>寫入"Delete"將會將類別刪除，類別原指定的項將會成為無類別項目</p>
        '; 
        
        $EditStr = '<div id="ChangeData">'.$EditStr.'</div>';

        $EditStr .= '<br><button onclick="submitForm()">更改</button>'; 

        // <p>改名：</p><select>

        echo '<form action="./'.$LinkVar.'&EditData='.$_GET['EditData'].'" method="post">'.$EditStr.'</form>';
    }

    ?>
    
    


</body>
</html>