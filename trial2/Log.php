<?php

class Log {

    private $login_url  = 'https://auth.zaim.net/';
    private $target_url = 'https://zaim.net/money';
    private $argv;
    private $email;
    private $password;
    private $month;
    private $ch;
    private $log_source;
    private $data;

    /**
     * ログインして入力履歴ページを取得
     */
    public function __construct($argv = null) {
    
        $this->argv = $argv;
        $this->set_account();

        $this->ch = curl_init(); //cURLセッションを初期化
        curl_setopt_array($this->ch, array(
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => '',
            CURLOPT_COOKIEJAR => '',
        ));
        $this->login();
        $this->get_log();
    }

    /**
     * アカウント情報を設定
     * 
     * @access private
     */
    private function set_account() {
        if(empty($this->argv)) { #引数が空の場合はブラウザで実行されている
            echo <<<EOM
                <div style='margin: 20px; padding: 20px; background-color: #fff;'>
                    <h1 style='text-align: center; line-height: 2;'>--+｡*.ﾟ:｡ようこそ！+｡*.ﾟ:｡ --</h1>
                    <p style='text-align: center; line-height: 1.5;'>
                        ほしさきひとみの今月の入力履歴を表示しています。<br>
                        コマンドラインから実行していただくと、ご自身のアカウントの履歴を確認いただけます。
                    </p>
                </div>
EOM;
            $this->email = 'zaim.trial@gmail.com';
            $this->password = 'zaimtest';

        } else {
            print ("\n───────── +｡*.ﾟ:｡ようこそ！+｡*.ﾟ:｡ ─────────\n");
            print ("Zaimの入力履歴を確認できるコマンドラインツールです。\n"); 
            $this->input_account();
        }
    }

    /**
     * アカウント情報を入力してもらう
     * 
     * @access private
     */
    private function input_account($error = null) {
        if($this->argv[1] AND !$error) {
            $this->email = $this->argv[1];
        } else {
            ob_end_clean();
            print ("\n▼ログイン用メールアドレスを入力してください(半角英数字)\n"); 
            $this->email = trim(fgets(STDIN));
        }

        if($this->argv[2] AND !$error) {
            $this->password = $this->argv[2];
        } else {
            print ("\n▼ログイン用パスワードを入力してください(半角英数字)\n"); 
            $this->password = trim(fgets(STDIN));
        }
    }

    /**
     * POST送信
     * 
     * @access private
     * @param string $url
     * @param array $params
     * @return string 送信後の画面
     */
    private function post($url, $params) {
        curl_setopt_array($this->ch, array(
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
        ));
        return curl_exec($this->ch);
    }

    /**
     * GET送信
     * 
     * @access private
     * @param string $url
     * @return string 送信後の画面
     */
    private function get($url) {
        curl_setopt_array($this->ch, array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPGET => true,
        ));
        return curl_exec($this->ch);
    }

    /**
     * ログイン処理
     * 
     * @access private
     */
    private function login() {
        $params = array(
            'data[User][email]' => $this->email,
            'data[User][password]' => $this->password,
            'submit'  => 'ログイン',
        );
        $source = $this->post($this->login_url, $params);
        preg_match('/https:\/\/zaim\.net\/user_session\/callback\?oauth_token=(\w+)&oauth_verifier=(\w+)/',
            $source, $matches
        );
        if(empty($matches[0])) {
            print ("\n[！]メールアドレスかパスワードが間違っています。再度入力してください。\n"); 
            $this->input_account(1);
        } else {
            $this->get($matches[0]);
        }
    }

    /**
     * 指定された月の入力履歴を取得
     * 
     * @access private
     */
    private function get_log() {
        if(empty($this->argv)) {
            $this->month = '';
        } elseif($this->argv[3]) {
            $this->month = $this->argv[3];
        } else {
            ob_end_clean();
            print ("\n▼入力履歴を表示したい年月を入力してください(記号なし/半角英数字/6ケタ) 入力例:201806\n"); 
            $this->month = trim(fgets(STDIN));
            // echo "\nなんじゃ".$this->month;
        }
        $param = $this->month ? '?month='.$this->month : '';
        $this->log_source = $this->get($this->target_url.$param);
    }

    /**
     * スクレイピング
     * 
     * @access public
     */
    public function scraping() {
        if(empty($this->argv)) { #引数が空の場合はブラウザで実行されている
            echo $this->log_source;
            exit;
        }
        #リストテーブルを抜き出す
        preg_match('/<table class=\'list\'>(.*?)<\/table>/si', $this->log_source, $matches);

        if(empty($matches[1])) {
            print ("\n[！]この月にはまだ記録がありません。あるいは、入力値が正しくありませんでした。\n"); 
        } else {
            #trごとの配列にする
            preg_match_all('/<tr>(.*?)<\/tr>/si', $matches[1], $tr_matches);

            #加工・表示
            $this->trim_data($tr_matches[1]);
            $this->show_data();
        }
    }

    /**
     * データ加工
     * 
     * @access private
     * @param string $tr_data
     */
    private function trim_data($tr_data) {
        $count = count($tr_data);
        $i = 0;
        foreach ($tr_data as $tr){
            if($i == 0) {
                $this->trim_th($tr);
            } else {
                $this->trim_td($tr, $count, $i);
            }
            $i++;
        }
    }

    /**
     * データ加工 > th
     * 
     * @access private
     * @param string $tr
     */
    private function trim_th($tr) {
        preg_match_all('/<th.*?>(.*?)<\/th>/si', $tr, $th_matches);
        $title = $th_matches[1];
        foreach ($title as $k => $v){
            if(mb_strlen($v)<3) {
                $title[$k] = $v.'　　';
            }
        }
        $this->data['title'] = $title;
    }

    /**
     * データ加工 > td
     * 
     * @access private
     * @param string $tr
     * @param int $count
     * @param int $i
     */
    private function trim_td($tr, $count, $i) {
        preg_match_all('/<td.*?>(.*?)<\/td>/si', $tr, $td_matches);
        $td_matches = str_replace(PHP_EOL, '', $td_matches[0]);

        if(($count-3) <= $i) {
            $this->trim_sum($td_matches, $count, $i);
        } else {
            $this->trim_log($td_matches, $i);
        }
    }

    /**
     * データ加工 > td > 合計
     * 
     * @access private
     * @param array $td_array
     * @param int $count
     * @param int $i
     */
    private function trim_sum($td_array, $count, $i) {
        if($i == ($count-3)) {
            preg_match('/.*?\'>(.*?)<.*?/i', $td_array[3], $sum_matches);
            $this->data['sum']['支出　　'] = $sum_matches[1];

        } elseif($i == ($count-2)) {
            preg_match('/.*?\'>(.*?)<.*?/i', $td_array[1], $sum_matches);
            $this->data['sum']['収入　　'] = $sum_matches[1];

        } elseif($i == ($count-1)) {
            preg_match('/.*?\'>(.*?)<.*?/i', $td_array[1], $sum_matches);
            $this->data['sum']['総額　　'] = $sum_matches[1];

        }
    }

    /**
     * データ加工 > td > ログ
     * 
     * @access private
     * @param array $td_array
     * @param int $i
     */
    private function trim_log($td_array, $i) {
        foreach ($td_array as $k => $v){
            if($k==2 OR $k==4) { #日付、金額
                preg_match('/.*?">(.*?)<.*?/i', $v, $data_matches);
                
            } elseif($k==3 OR $k==5 OR $k==6) { #カテゴリ、出金、入金
                preg_match('/.*?data-title="(.*?)".*?/i', $v, $data_matches);
            
            } elseif(7<=$k) { #お店、品目、メモ
                preg_match('/.*?title=\'(.*?)\'>.*?/i', $v, $data_matches);

            }
            $this->data['log'][$i][$k] = $data_matches[1] ? $data_matches[1] : '-';
            unset($data_matches);
        }
    }

    /**
     * データ表示
     * 
     * @access private
     */
    private function show_data() {
        $month = $this->month ? preg_replace('/^.{0,4}+\K/us', '年', $this->month).'月' : '今月';
        echo "\n──────────────────────────────\n";
        echo $month."の入力履歴\n";
        foreach ($this->data['log'] as $l){
            echo "──────────────────────────────\n";
            foreach ($l as $k => $v){
                if($k<=1) {
                    continue;
                } else {
                    echo $this->data['title'][$k]."\t".$v."\n";
                }
            }
        }
        echo "\n──────────────────────────────\n";
        echo $month."の合計\n";
        echo "──────────────────────────────\n";
        foreach ($this->data['sum'] as $k => $v){
            echo $k."\t".$v."\n";
        }
        echo "──────────────────────────────\n";
        echo "\nご利用ありがとうございました！\n";
    }
}

$log = new Log($argv);
$log->scraping();

?>