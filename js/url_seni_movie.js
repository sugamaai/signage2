document.addEventListener("DOMContentLoaded", function () {

    var mylink = [
        "./index.html",
        "./signage02.html",
        "./signage_youtube02.html",  // 2026年8月リニューアルまで
        "./signage_youtube04.html",  // クーポンの使い方　リニューアルまで？
        "./signage_youtube05.html",  // 会員登録方法　リニューアルまで？
        "./signage_202604renewal.html", // 2026年8月リニューアルまで
        "./mvvlogo.html",
       "./signage50_benkyokai.html",
    ];

    // signage50の表示終了日時、および管理画面から追加されたページは
    // admin/upload_signage50.php が書き出すファイルから読み込む
    Promise.all([
        fetch("./img/info/signage50_meta.json", { cache: "no-store" })
            .then(function (res) { return res.ok ? res.json() : {}; })
            .catch(function () { return {}; }),
        fetch("./img/info/signage_pages.json", { cache: "no-store" })
            .then(function (res) { return res.ok ? res.json() : []; })
            .catch(function () { return []; }),
    ]).then(function (results) {
        var signage50Until = results[0] && results[0].until ? results[0].until : null;
        var addedPages = Array.isArray(results[1]) ? results[1] : [];
        init(signage50Until, addedPages);
    });

    function init(signage50Until, addedPages) {
        var now = new Date();
        var activeLinks = mylink.filter(function (path) {
            if (path === "./signage50_benkyokai.html" && signage50Until) {
                return now <= new Date(signage50Until);
            }
            return true;
        });

        addedPages.forEach(function (page) {
            if (!page.until || now <= new Date(page.until)) {
                activeLinks.push("./" + page.html);
            }
        });

        console.log("====================================");
        console.log("ページ読込完了");
        console.log("現在のURL:", location.href);
        console.log("表示対象URL一覧（期限切れを除く）");
        console.log("====================================");

        activeLinks.forEach(function (path, index) {
            var fullUrl = new URL(path, location.href).href;
            console.log((index + 1) + ".", fullUrl);
        });

        console.log("====================================");
        console.log("自動遷移タイマーを開始します");
        console.log("====================================");

        function timerFunc() {
            var randomIndex = Math.floor(Math.random() * activeLinks.length);
            var selectedPath = activeLinks[randomIndex];
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
    }
});