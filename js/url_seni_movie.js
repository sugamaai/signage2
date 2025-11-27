document.addEventListener("DOMContentLoaded", function () {

    console.log("ページが読み込まれました。JavaScriptが動作しています。"); // デバッグ用



    function timerFunc() {

        var mylink = [

            "./index.html", // 26/2/28まで

            "./signage02.html",

            "./signage03.html",

            "./signage_youtube.html",

            "./signage_youtube01.html",

            "./mvvlogo.html",

            "./signage21_iaeon4th.html", // 11/30まで

            "./signage27_sumikko.html", // 11/30まで

            "./signage28_blackfriday.html", // 11/30まで

            "./index_blackfriday.html", // 壁紙ページ（11月いっぱい）

            "./signage29_lotte.html", // 12/3まで

            "./signage30_sandwich.html", // 12/3まで

            "./signage32_maru.html", // 2026/1/7まで

            "./signage33_nabe.html", // 2026/1/7まで

            "./signage31_benkyokai.html", // 2026/1/21まで

        ];



        console.log("指定したページへの遷移が開始されます"); // デバッグ用

        // 遷移を実行するには下記のコードを有効にする

        window.location.href = mylink[Math.floor(Math.random() * mylink.length)];

    }



    var timeLimit = 30 * 1000; // 制限時間

    var timerId = setTimeout(timerFunc, timeLimit); // 初回のタイマーをセット

    console.log("タイマーが設定されました"); // デバッグ用



    function cancelAutoRedirect() {

        clearTimeout(timerId); // タイマーをクリア（キャンセル）

        console.log("タッチされたので遷移をキャンセルします"); // デバッグ用

    }



    // タッチイベントを監視して遷移をキャンセル

    document.addEventListener("click", cancelAutoRedirect);

    document.addEventListener("touchstart", cancelAutoRedirect);

});