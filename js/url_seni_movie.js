document.addEventListener("DOMContentLoaded", function () {

    console.log("ページが読み込まれました。JavaScriptが動作しています。"); // デバッグ用



    function timerFunc() {

        var mylink = [

            "./index.html", // 26/2/28まで

            "./signage02.html",

            "./signage03.html",

            "./signage_youtube.html",

            "./signage_youtube01.html",

            "./signage25_tokachi.html", // 10/22まで

            "./signage22_lab.html", // 10/29まで

            "./signage26_ajinomoto.html", // 10/29まで

            "./signage19_aki.html", // 10/31まで

            "./signage24_Suntory.html", // 11/26まで

            "./signage21_iaeon4th.html", // 11/30まで

            "./mvvlogo.html",

            "./index_blackfriday.html", // 壁紙ページ（11月いっぱい）

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