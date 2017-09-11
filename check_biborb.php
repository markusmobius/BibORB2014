<html>
<head>
<title>Test BibORB installation</title>
<style type="text/css">

.error {
    font-weight:bold;
    color:red;
}

.ok {
    font-weight:bold;
    color:green;
}

h1 {
    text-align:center;
}

h2 { text-align:center; }

.report {
    margin:auto;
    border-collapse:collapse;
}

.report tr {
    border: solid 1px black;
}

.report th {
    color:white;
    background-color:#777;
    text-align:left;
//    width:200px;
}

.report td {
    text-align:left;
//    width:300px;
    padding-left:1em;
    background-color:#ddd;
}

</style>
</head>
<body>

<?php

include("config.php");
include("php/third_party/Tar.php");
$OS_TYPE = strtoupper(substr(PHP_OS, 0, 3));
$XSLT_LOADED = extension_loaded('xsl');
if($OS_TYPE == "Win"){
    $XSLT_MODULE = "php_xsl.dll";
}
else{
    $XSLT_MODULE = "xsl.so";
}

$YES_REPORT = "<span class='ok'>YES</span>";
$NO_REPORT = "<span class='error'>NO</span>";
?>

<h2>Information</h2>
<table class="report">
    <tbody>
<!--        <tr>
            <th>BibORB directory</th>
            <td><?php echo realpath("."); ?></td>
        </tr>-->
        <tr>
            <th>PHP version</th>
            <td><?php echo phpversion() ?></td>
        </tr>
        <tr>
            <th>OS</th>
            <td><?php echo PHP_OS; ?></td>
        </tr>
        <tr>
            <th>XSLT module loaded:</th>
            <td><?php echo ($XSLT_LOADED ? $YES_REPORT : $NO_REPORT) ?></td>
        </tr>
     <!--       <?php
            if(!$XSLT_LOADED){
                echo "<tr><th>Loading extensions is allowed?</th><td>";
                if(!(bool)ini_get( "enable_dl" ) || (bool)ini_get( "safe_mode" )){
                    echo $NO_REPORT;
                }
                else{
                    echo $YES_REPORT;
                }
                echo "<tr><th>PHP extendion directory</th><td>".realpath(ini_get("extension_dir"))."</td></tr>";
                echo "<tr><td>XSLT module ($XSLT_MODULE) is present in extension_dir?</td><td>";
                if(!file_exists(ini_get("extension_dir")."/".$XSLT_MODULE)) {
                    echo $NO_REPORT;
                }
                else{
                    echo $YES_REPORT;
                }
                echo "</td></tr>";
            }
             ?>-->
        <tr>
            <th>Pear Module</th>
            <td><?php echo (class_exists('Archive_Tar') ? $YES_REPORT : $NO_REPORT); ?></td>
        </tr>
        <tr>
            <th>Write access to bibs repository?</th>
            <td><?php echo (is_writable("./bibs") ? $YES_REPORT : $NO_REPORT); ?></td>
        </tr>
        <tr>
            <th>Write access to bibs/.trash repository?</th>
            <td><?php echo (is_writable("./bibs/.trash") ? $YES_REPORT : $NO_REPORT); ?></td>
        </tr>
        <tr>
            <th>Maximum size of uploadable files.</th>
            <td><?php echo ini_get('upload_max_filesize');?></td>
        </tr>

        <tr>
            <th>Authentication activated</th>
<td><?php echo (DISABLE_AUTHENTICATION ? $NO_REPORT : $YES_REPORT);?></td>
        </tr>

        <?php
if(!DISABLE_AUTHENTICATION){
    echo "<tr>";
    echo "<th>Authentication used</th>";
    echo "<td>".AUTH_METHOD."</td>";
    echo "</tr>";
}
if(AUTH_METHOD == "files" && !DISABLE_AUTHENTICATION){

    echo "<tr><th>biborb/data/auth_files/bib_users.txt exists?</th>";
    echo "<td>".(file_exists("./data/auth_files/bib_users.txt") ? $YES_REPORT : $NO_REPORT)."</td></tr>";
    echo "<tr><th>biborb/data/auth_files/bib_access.txt exists?</th>";
    echo "<td>".(file_exists("./data/auth_files/bib_access.txt") ? $YES_REPORT : $NO_REPORT)."</td></tr>";
    echo "<tr><th>Preferences can be created (auth_files write permission)?</th>";
    echo "<td>".(is_writable("./data/auth_files/") ? $YES_REPORT : $NO_REPORT)."</td></tr>";
}
?>
    </tbody>
</table>

</body>
</html>
