<?php

function fdbg(mixed ...$values)
{
    FastDbgPHP::fdbg(...$values);
}

class FastDbgPHP
{
    static private bool     $isDevelopementMode = false;
    static private string   $projectName = "";
    static private ?float   $startTime = null;
    static private bool     $isExit = false;
    static private bool     $clickToCopy = true;
    static private int      $buttomId = 0;
    static private array    $defaltValues = ['##GET', '##POST'];
    static private array    $styles = [
        'box'               => 'font-family: ui-monospace, monospace;
                                color: #363636;
                                background-color: #F6F8FC;
                                border-radius: 5px;
                                border: 1px solid #DCDCDC;
                                position: relative;
                                padding: 10px 0px 15px 15px;
                                margin: 10px 4px;',
        'header'            => 'color: #363636;
                                font-size: 16px;
                                padding: 0 0 10px;',
        'titles'            => 'color: #363636;
                                border: 1px solid #DCDCDC;
                                font-size: 18px',
        'info'              => 'border-top: 1px solid #DCDCDC;
                                font-size: 14px;
                                text-align: left;
                                padding: 5px 10px;
                                word-break: break-word;',
        'description'       => 'border-top: 1px solid #DCDCDC;
                                font-size: 12px;
                                text-align: left;
                                padding: 5px 10px;
                                word-break: break-word;
                                cursor: pointer;',
        'descriptionCredit' => 'border-top: 1px solid #DCDCDC;
                                font-size: 12px;
                                text-align: right;
                                padding: 5px 10px;
                                word-break: break-word;',
        'arrays'            => 'border: 1px solid #DCDCDC;
                                border-radius: 5px;
                                background-color: #EEE;
                                font-size: 12px;
                                text-align: left;
                                padding: 5px 10px;
                                margin: 1px;
                                word-break: break-word;',
        'descriptionArrays'     => 'border: 1px solid #DCDCDC;
                                border-radius: 5px;
                                background-color: #FFF;
                                font-size: 12px;
                                text-align: left;
                                padding: 5px 10px;
                                margin: 1px;
                                word-break: break-word;'
    ];

    public function __construct()
    {
    }

    static public function setDevelopmentMode(bool $isDevelopementMode): void
    {
        static::$isDevelopementMode = $isDevelopementMode;
    }

    static public function setProjectName(string $projectName): void
    {
        static::$projectName = $projectName;
    }

    static public function setStyles(array $styles): void
    {
        static::$styles = $styles + static::$styles;
    }

    static public function setDefaultValues(array $defaltValues): void
    {
        static::$defaltValues = $defaltValues;
    }

    static public function setInicialTime(float $startTime): void
    {
        static::$startTime = $startTime;
    }

    static public function setIsExit(bool $isExit): void
    {
        static::$isExit = $isExit;
    }

    static public function setclickToCopy(bool $clickToCopy): void
    {
        static::$clickToCopy = $clickToCopy;
    }


    /**
     * Function of the Debug
     *
     * @param mixed ... $values
     * ##TRACE - Show traceback
     * ##GET, ##POST, ##SERVER, ##FILES, ##COOKIE, ##SESSION, ##REQUEST, ##ENV - Show global var
     * ##EXIT - Finish the code
     * ##TIME
     */
    static public function fdbg(mixed ...$values): void
    {
        if (static::$isDevelopementMode) {
            if (count($values) == 0) {
                self::fdbg(...static::$defaltValues);
                return;
            }
            
            static::scriptCopyText();

            $debug_arr = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $debug_arr = end($debug_arr);
            $line = $debug_arr['line'];
            $file = $debug_arr['file'];

            echo '<div style="' . static::$styles['box'] . "\">";
            echo '<div style="' . static::$styles['header'] . "\">";
            if (static::$projectName) {
                echo "<div>💼 Project: " . static::$projectName . "</div>";
            }
            echo "<div>🪲 FAST_DBG_PHP: file $file on line line $line</div>";
            echo "</div>";

            $hasexit = static::$isExit;

            echo '<div style="display: grid; grid-template-columns: 30% 70%;">';
            foreach ($values as $value) {
                if (is_null($value)) {
                    $type = get_debug_type($value);
                    static::generateRow("🏷️ Variable [$type]", "null");
                } elseif (is_bool($value)) {
                    $type = get_debug_type($value);
                    static::generateRow("🏷️ Variable [$type]", $value == true ? "True" : "False");
                } elseif ($value == "##GET") {
                    static::generateDetailRow("💾 GET params", $_GET);
                } elseif ($value == "##POST") {
                    static::generateDetailRow("💾 POST params", $_POST);
                } elseif ($value == "##SERVER") {
                    static::generateDetailRow("🖥️ Server params", $_SERVER);
                } elseif ($value == "##FILES") {
                    static::generateDetailRow("📂 File params", $_FILES);
                } elseif ($value == "##TIME") {
                    if (static::$startTime != null)
                        $time = round(microtime(true) - static::$startTime, 4) . " seconds";
                    else
                        $time = 'Define variable $startTime at the beginning of the code.';
                    static::generateRow("⏱️ Time", $time);
                    continue;
                } elseif ($value == "##TRACE") {
                    static::generateTracebackRow("🚦 Traceback");
                    continue;
                } elseif ($value == "##COOKIE") {
                    static::generateDetailRow("🍪 Cookies", $_COOKIE);
                } elseif ($value == "##SESSION") {
                    if (session_status() == PHP_SESSION_ACTIVE) {
                        static::generateDetailRow("📅 Sessions", $_SESSION);
                    } else {
                        static::generateDetailRow("📅 Sessions", "<i>Session not active</i>");
                    }
                } elseif ($value == "##REQUEST") {
                    static::generateDetailRow("🔗 Requests", $_REQUEST);
                } elseif ($value == "##ENV") {
                    static::generateDetailRow("🏠 Environment", $_ENV);
                } elseif ($value == "##EXIT") {
                    $hasexit = true;
                } else {
                    $type = get_debug_type($value);
                    static::generateDetailRow("🏷️ Variable [$type]", $value);
                }
            }

            $LePampim = '<a href ="https://github.com/LePampim">LePampim</a>';
            $Mocno = '<a href ="https://github.com/mocno">Mocno</a>';
            $fastDbgPHP = '<a href ="https://github.com/LePampim/FastdbgPHP">Fast Debug PHP</a>';

            
            
            $creditMsg = "$fastDbgPHP developed by $LePampim and $Mocno. ";
            if($hasexit)
                $creditMsg .= "[❌ exited] ";
            if (static::$clickToCopy)
                $creditMsg .= "[📑 click to copy] ";

            static::generateRowCredit($creditMsg);

            echo "</div>";
            echo '</div>';

            if ($hasexit) exit;
        }
    }

    static private function generateDetailRow(string $info, mixed $description)
    {
        if (is_string($description))
            $info .= " [" . strlen($description) . "]";
        elseif (is_array($description))
            $info .= " [" . count($description) . "]";

        echo '<div style="' . static::$styles['info'] . '">'.$info.'</div>';
        echo '<div style="' . static::$styles['description'] . '">';

        if (is_string($description)) {
            static::generateSimpleTable($description);
        } elseif (is_array($description)) {
            if ($description) {
                static::generateArrayTable((array) $description);
            } else
                static::generateSimpleTable("<i>Empty</i>");
        } elseif (is_object($description))
            static::generateArrayTable((array) $description);
        else 
            static::generateSimpleTable($description);

        echo '</div>';
    }

    static private function generateRow(string $info, mixed $description)
    {
        static::generateContent($info, static::$styles['info']);
        echo '<div style="' . static::$styles['description'] . '">';
        static::generateSimpleTable($description);
        echo '</div>';
    }
    static private function generateTracebackRow(string $info)
    {
        static::generateContent($info, static::$styles['info']);
        echo '<div style="' . static::$styles['description'] . '">';
        echo '<div style="' . static::$styles['descriptionArrays'] . "\"><pre>";
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        echo '</pre></div></div>';
    }
    static private function generateRowCredit(mixed $description)
    {
        static::generateContent("", static::$styles['info']);
        static::generateContent($description, static::$styles['descriptionCredit']);
    }

    static private function generateSimpleTable($value)
    {
        static::generateContent($value, static::$styles['descriptionArrays'], true);
    }

    static private function generateArrayTable(array $arrays)
    {
        echo '<div style="display: grid; grid-template-columns: 20% 80%;">';
        foreach ($arrays as $key => $value) {
            static::generateContent($key, static::$styles['arrays'], true);
            if (is_string($value))
                static::generateContent($value, static::$styles['descriptionArrays'], true);
            else {
                if (is_array($value)) {
                    if ($value)
                        static::generateArrayTable((array) $value);
                    else
                        static::generateContent("", static::$styles['descriptionArrays'], true);
                } elseif (is_object($value)) {
                    static::generateArrayTable((array) $value);
                } else {
                    static::generateContent($value, static::$styles['descriptionArrays'], true);
                }
            }
        }
        echo '</div>';
    }

    static private function generateContent(string $value, string $style, bool $copy = false) {
        if ($copy and static::$clickToCopy) {
            $id = 'idCopy'.static::$buttomId += 1;
            echo '<div title="click to copy." style="' . $style . "\" name=\"$id\" id='$id' onclick=\"copyTextFdbg(this.id)\" >$value</div>";
        } else
            echo '<div style="' . $style . "\"\" >$value</div>";
    }

    static private function scriptCopyText (){
        echo "
        <script type=\"text/javascript\">
        function copyTextFdbg(copyId) {
            var textCopy = document.getElementById(copyId);
            var tmpFdbg = document.createElement('input');
            tmpFdbg.value = textCopy.textContent
            textCopy.appendChild(tmpFdbg);
            tmpFdbg.select();
            tmpFdbg.setSelectionRange(0, 99999);
            document.execCommand(\"copy\");
            tmpFdbg.remove();
        }
      </script>";
    }
    static private function CSSSSSSS (){
        echo "<style>
            .fdbg-
      </style>";
    }
}
