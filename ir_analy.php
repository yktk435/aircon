<?php

class IrCodeAnalytics
{
    protected $T8;
    protected $T4;
    protected $bynalys = [];
    protected $codes = [];

    public function __construct($code_file)
    {
        $this->codes = $this->analyticsCodes($code_file);
    }

    public function toBynalyCode()
    {
        $bynaly_codes = [];

        foreach ($this->codes as $mode => $codes) {
            $bynaly_codes[$mode]['T8'] = $codes[$mode]['T8'];
            $bynaly_codes[$mode]['T4'] = $codes[$mode]['T4'];

            foreach ($codes['codes'] as $i => $codes) {
                if (!isset($bynaly_codes[$mode]['codes'][$i])) {
                    $bynaly_codes[$mode]['codes'][$i] = [];
                }

                $bynaly_codes[$mode]['codes'][$i][] = $this->codeToBynaly($codes);
            }
        }

        return $bynaly_codes;
    }

    public  function toHexCode()
    {
        $hex_codes = [];

        foreach ($this->codes as $mode => $codes) {
            $hex_codes[$mode] = [];

            foreach ($codes['codes'] as $i => $code) {
                $bynaly_codes = $this->codeToBynaly($code);

                foreach ($bynaly_codes as $bynaly) {
                    if (!isset($hex_codes[$mode][$i])) {
                        $hex_codes[$mode][$i] = [];
                    }

                    $hex_codes[$mode][$i][] = self::bynalyToHex($bynaly);
                }
            }
        }

        return $hex_codes;
    }

    public static function bynalyToHex(string $bynaly)
    {
        return dechex(bindec($bynaly));
    }

    public  function printHex()
    {
        $hex_codes = $this->toHexCode();

        foreach ($hex_codes as $mode => $codes) {
            echo  "mode : {$mode}" . PHP_EOL;

            foreach ($codes as  $_codes) {
                echo  PHP_EOL;

                foreach ($_codes as $code) {
                    echo sprintf('%02s', strtoupper($code)) . ' ';
                }
            }
            echo  PHP_EOL . PHP_EOL;
        }
    }

    protected  function codeToBynaly($codes)
    {
        $bynaly  = [];
        $bynalys = [];

        foreach ($codes as $code) {
            if ($this->is_zero($code)) {
                $bynaly[] = 0;
            } else {
                $bynaly[] = 1;
            }

            if (count($bynaly) === 8) {
                $bynalys[] = implode('', $bynaly);
                $bynaly    = [];
            }
        }

        return $bynalys;
    }

    protected  function is_zero($codes)
    {
        $turning_on_time  = (string) $codes[0];
        $turning_off_time = (string) $codes[1];

        if (strlen($turning_on_time) === strlen($turning_off_time)) {
            return true;
        }

        return false;
    }

    protected function analyticsCodes($code_file)
    {
        if (!$file = file_get_contents($code_file)) {
            throw new RuntimeException('赤外線コードファイルがない');
        }

        $codes = json_decode($file);

        $analytics_codes = [];
        foreach ($codes as $mode => $mode_code) {
            $analytics_codes[$mode] = [
                'T8'     => null,
                'T4'     => null,
                'codes'  => [],
            ];
            $code_index = 0;
            $tmps = [];

            foreach ($mode_code as $i => $code) {
                if (isset($analytics_codes[$mode]['T4'])) {
                    if ($analytics_codes[$mode]['T4'] === $code) {
                        $code_index++;
                        $analytics_codes[$mode]['codes'][$code_index] = [];

                        continue;
                    }
                }

                if (isset($analytics_codes[$mode]['T8'])) {
                    if ($analytics_codes[$mode]['T8'] < $code) {
                        $tmps = [];

                        continue;
                    } elseif ($analytics_codes[$mode]['T8'] === $code) {
                        continue;
                    }
                }

                if ($i === 0) {
                    $analytics_codes[$mode]['T8'] = $code;
                } elseif ($i === 1) {
                    $analytics_codes[$mode]['T4'] = $code;
                } else {
                    $tmps[] = $code;
                }

                if (count($tmps) === 2) {
                    $analytics_codes[$mode]['codes'][$code_index][] = $tmps;
                    $tmps                                         = [];
                }
            }
        }
        return $analytics_codes;
    }
}
$r = new IrCodeAnalytics('test_codes');
print_r($r->printHex());
