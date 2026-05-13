document.addEventListener("DOMContentLoaded", function () {

    var mylink = [
        "./index.html",
        "./signage02.html",
        "./signage_youtube02.html",  // 2026年8月リニューアルまで
        "./signage_youtube03.html",  // アオハル祭 6月30日まで
        "./signage_202604renewal.html", // 2026年8月リニューアルまで
        "./mvvlogo.html",
        "./signage46_degi_talk.html"    // 2026/6/4(木)17時まで
    ];

    console.log("====================================");
    console.log("ページ読込完了");
    console.log("現在のURL:", location.href);
    console.log("表示対象URL一覧");
    console.log("====================================");

    mylink.forEach(function (path, index) {
        var fullUrl = new URL(path, location.href).href;
        console.log((index + 1) + ".", fullUrl);
    });

    console.log("====================================");
    console.log("自動遷移タイマーを開始します");
    console.log("====================================");

    function timerFunc() {
        var randomIndex = Math.floor(Math.random() * mylink.length);
        var selectedPath = mylink[randomIndex];
        var fullUrl = new URL(selectedPath, location.href).href;

        console.log("====================================");
        console.log("自動遷移を実行します");
        console.log("選択番号:", randomIndex + 1);
        console.log("遷移先フルURL:", fullUrl);
        console.log("====================================");

        window.location.href = fullUrl;
    }

    var timeLimit = 30 * 1000;
    var timerId = setTimeout(timerFunc, timeLimit);

    console.log("タイマー設定:", timeLimit / 1000 + "秒後に自動遷移");

    function cancelAutoRedirect() {
        clearTimeout(timerId);

        console.log("====================================");
        console.log("ユーザー操作を検知しました");
        console.log("自動遷移をキャンセルしました");
        console.log("操作時のURL:", location.href);
        console.log("====================================");
    }

    document.addEventListener("click", cancelAutoRedirect, { once: true });
    document.addEventListener("touchstart", cancelAutoRedirect, { once: true });
});