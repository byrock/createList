<input type="button" id="btnExport" value="Export" />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.22/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
<script type="text/javascript">
    $("body").on("click", "#btnExport", function () {
        html2canvas($('#export')[0], {
            onrendered: function (canvas) {
                var data = canvas.toDataURL();
                var docDefinition = {
                    content: [{
                        image: data,
                        width: 500
                    }]
                };
                pdfMake.createPdf(docDefinition).download("Table.pdf");
            }
        });
    });
</script>

<?php

$form = '<form action="createExcel.php" method="post" enctype="multipart/form-data" >
            <table>
                <tr>
                    <td><input type="file" name="file" id="fileToUpload"></td>
                    <td><input type="submit" value="Upload File" name="submit"></td>
                </tr>
            </table>
        </form>';

$file = isset($_FILES['file']) ? $_FILES['file'] : '';
if ( isset($_POST["submit"]) ) {

    if ( isset($_FILES["file"])) {
        require_once('HtmlExcel.php');
        $html = '';
        //if there was an error uploading the file
        if ($_FILES["file"]["error"] > 0) {
            echo "Return Code: " . $_FILES["file"]["error"] . "<br />";

        }else {
            $storagename = "uploaded_file.csv";
            move_uploaded_file($_FILES["file"]["tmp_name"], "upload/" . $storagename);

            $csv = array_map('str_getcsv', file('upload/uploaded_file.csv'));
            $html .= '<table border="1" id="export"><tbody><tr>';
            $line_limit = 3;
            $count = 0;
            foreach($csv as $line){
                $count++;
                if($count > $line_limit){
                    $count = 1;
                    $html .= '</tr><tr>';
                }
                $columns = getColumns($line[0]);
                $cell = createCell($columns);
                $html .= "<td>$cell</td>";

            }
            $html .= '</tr></tbody></table>';
        }
//        $xls = new HtmlExcel();
//        $xls->addSheet("List", $html);
//        $xls->headers();
//        echo $xls->buildFile();
    } else {
        echo "No file selected <br />";
    }

//    $file="demo.xls";
//    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
//    header("Content-Disposition: attachment; filename=items.xls");
//    header("Cache-Control: max-age=0");
    echo $html;

}else{
    echo $form;
}

function getColumns($line){
    $columns = [];
    if(strpos($line, ';') !== false){
        $columns = explode(';', $line);
    }elseif(strpos($line, '|') !== false){
        $columns = explode('|', $line);
    }elseif(strpos($line, ';') !== false){
        $columns = explode(';', $line);
    }
    return $columns;
}

function createCell($columns){
    $turkish = [ 'ş' => 's', 'ü' => 'u', 'ö' => 'o', 'İ' => 'I', 'ğ' => 'g', 'ı' => 'i', 'ç' => 'c',
                'Ş' => 'S', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G', 'Ç' => 'C'];

    $code = $columns[0];
    $src = 'images/'.$code.'.jpg';

    if(file_exists($src)){
        $cell = "<img src='$src' height='100'" ;
    }else{
        $cell = "<img src='images/no-image.jpg' height='100'" ;
    }
    $values = [];
    foreach($columns as $index => $value){
        foreach($turkish as $tr => $en){
            $value = str_replace($tr, $en, $value);
        }
        $values[] = $value;
    }

    if(!empty($values)){
        $cell .= '<br /><br />'.implode('<br />', $values);
    }
    return $cell;
}

?>
