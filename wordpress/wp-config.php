<?php
/**
 * The base configurations of the WordPress.
 *
 * このファイルは、MySQL、テーブル接頭辞、秘密鍵、言語、ABSPATH の設定を含みます。
 * より詳しい情報は {@link http://wpdocs.sourceforge.jp/wp-config.php_%E3%81%AE%E7%B7%A8%E9%9B%86 
 * wp-config.php の編集} を参照してください。MySQL の設定情報はホスティング先より入手できます。
 *
 * このファイルはインストール時に wp-config.php 作成ウィザードが利用します。
 * ウィザードを介さず、このファイルを "wp-config.php" という名前でコピーして直接編集し値を
 * 入力してもかまいません。
 *
 * @package WordPress
 */

// 注意: 
// Windows の "メモ帳" でこのファイルを編集しないでください !
// 問題なく使えるテキストエディタ
// (http://wpdocs.sourceforge.jp/Codex:%E8%AB%87%E8%A9%B1%E5%AE%A4 参照)
// を使用し、必ず UTF-8 の BOM なし (UTF-8N) で保存してください。

// ** MySQL 設定 - こちらの情報はホスティング先から入手してください。 ** //
define (WP_SITEURL, 'http://wp-test.interworks.jp/');

/** WordPress のためのデータベース名 */
define('DB_NAME', 'wordpress');

/** MySQL データベースのユーザー名 */
define('DB_USER', 'root');

/** MySQL データベースのパスワード */
define('DB_PASSWORD', 'root');

/** MySQL のホスト名 */
define('DB_HOST', 'localhost');

/** データベースのテーブルを作成する際のデータベースのキャラクターセット */
define('DB_CHARSET', 'utf8');

/** データベースの照合順序 (ほとんどの場合変更する必要はありません) */
define('DB_COLLATE', '');

/**#@+
 * 認証用ユニークキー
 *
 * それぞれを異なるユニーク (一意) な文字列に変更してください。
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org の秘密鍵サービス} で自動生成することもできます。
 * 後でいつでも変更して、既存のすべての cookie を無効にできます。これにより、すべてのユーザーを強制的に再ログインさせることになります。
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'caZV~w2-)mXZSwb|6bDi+zqwmVy./Ue@7i:3lZZwiaN-Q+=w IG-Ad>eYRuDd*]+');
define('SECURE_AUTH_KEY',  'xZ*k_W8pbbypeI aU<v`lN^)|;>T0?i|s<3L+*it#*XIK_Dko@n}G)C>Q_e/E#O&');
define('LOGGED_IN_KEY',    '0+IzI0/dGA7omoz;2I&,?ZW&?bTj?-3qDPMBWD*N/M~CrAEJe%:OZZYD=.-0(`Q@');
define('NONCE_KEY',        ';z];1PSzy!n^up~AcoVHX(pOvq8aoQdJ_*D%Jxbl(kUZjx!KYLSO|<-*l^C<)Kju');
define('AUTH_SALT',        'qa+,Zq^#ym(Xw%0W863/]W1^e6zD@#ucqJX3j%6T1qOKjcd/PMMJ^#YN+.a)S+gq');
define('SECURE_AUTH_SALT', 'vd+be+M{P;8AqX+Muxa-dDg+qk),-]?qB,_!>+2p[fH$vF#211TTg.xVwwU=+u+l');
define('LOGGED_IN_SALT',   '8Ov:0oerMB8@gE(JueAhgJ4FdY?IF%RRB4RoX1DeG+6au5mM<l}~4/cLfh_`#qah');
define('NONCE_SALT',       '6Xv#,wOUq.+k<WqRg4Ak7A%j!!52]IMyYEulug-t&!!&2fki@I@_+M`SV =WuX*O');

/**#@-*/

/**
 * WordPress データベーステーブルの接頭辞
 *
 * それぞれにユニーク (一意) な接頭辞を与えることで一つのデータベースに複数の WordPress を
 * インストールすることができます。半角英数字と下線のみを使用してください。
 */
$table_prefix  = 'wp_';

/**
 * ローカル言語 - このパッケージでは初期値として 'ja' (日本語 UTF-8) が設定されています。
 *
 * WordPress のローカル言語を設定します。設定した言語に対応する MO ファイルが
 * wp-content/languages にインストールされている必要があります。例えば de_DE.mo を
 * wp-content/languages にインストールし WPLANG を 'de_DE' に設定することでドイツ語がサポートされます。
 */
define('WPLANG', 'ja');

/**
 * 開発者へ: WordPress デバッグモード
 *
 * この値を true にすると、開発中に注意 (notice) を表示します。
 * テーマおよびプラグインの開発者には、その開発環境においてこの WP_DEBUG を使用することを強く推奨します。
 */
define('WP_DEBUG', false);

/* 編集が必要なのはここまでです ! WordPress でブログをお楽しみください。 */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
