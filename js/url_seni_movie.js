document.addEventListener("DOMContentLoaded", function () {

    console.log("ページが読み込まれました。JavaScriptが動作しています。"); // デバッグ用



    function timerFunc() {

        var mylink = [

            "./index.html", // 9/30まで

            "./signage02.html",

            "./signage03.html",

            "./signage_youtube.html",
			
			"./signage_youtube01.html",

            "./signage13_mirai.html",  // 8/17まで

            "./signage14_kirin.html", // 8/13まで

            "./signage15_zone.html", // 8/27まで

            "./signage16_id.html", // 8/31まで

            "./signage17_nonde.html", // 9/3まで

            "./signage18_curry.html", // 8/27まで



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