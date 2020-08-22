'use strict';

(function () {
    function init() {
        var router = new Router([
            new Route('home', 'home.html', true),
            new Route('login', 'login.html'),
            new Route('exams', 'exams.html'),
            new Route('certifications', 'certifications.html')
        ]);
    }
    init();
}());
