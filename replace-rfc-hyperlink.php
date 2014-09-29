<?PHP

$dir = 'D:\\rfc7230\\tools.ietf.org\\html';

$a = '<a href="rfc7230';

$dh = dir($dir);
$files = array();
$fileBaseNames = array();
while ($fileName = $dh->read()){
    if (!in_array($fileName, array('.','..')) && !strstr($fileName, '.htm')){
        echo $fileName . "\n";
        if (!file_exists( $dir .'\\'.$fileName.'.htm')){
            echo "rename " . $dir .'\\'.$fileName. ' ' .  $fileName.'.htm' . "\n";
            exec("rename " . $dir .'\\'.$fileName. ' ' .  $fileName.'.htm');
            $fileName .= '.htm';
            $files[] = $dir . '\\'.$fileName;
            $fileBaseNames[basename($fileName, '.htm')] = $fileName;
        } else {
            exec("del /f " . $dir .'\\'.$fileName);
        }
    }
}

foreach ($files as $fileName) {
    echo 'processing '. $fileName . "\n";
    $fileContent = file_get_contents($fileName);
    if (!$fileContent){
        echo "failed: ".$fileContent. "\n";
        continue;
    }
    foreach ($fileBaseNames as $baseName => $nameWithExt) {
        $fileContent = str_replace('href="'.$baseName, 'href="'.$nameWithExt, $fileContent);
    }
    file_put_contents($fileName, $fileContent);
}



