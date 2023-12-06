/* http://prismjs.com/download.html?themes=prism&languages=clike+php&plugins=line-highlight+line-numbers */
var _self =
        "undefined" != typeof window
            ? window
            : "undefined" != typeof WorkerGlobalScope &&
              self instanceof WorkerGlobalScope
            ? self
            : {},
    Prism = (function () {
        var e = /\blang(?:uage)?-(\w+)\b/i,
            t = 0,
            n = (_self.Prism = {
                util: {
                    encode: function (e) {
                        return e instanceof a
                            ? new a(e.type, n.util.encode(e.content), e.alias)
                            : "Array" === n.util.type(e)
                            ? e.map(n.util.encode)
                            : e
                                  .replace(/&/g, "&amp;")
                                  .replace(/</g, "&lt;")
                                  .replace(/\u00a0/g, " ");
                    },
                    type: function (e) {
                        return Object.prototype.toString
                            .call(e)
                            .match(/\[object (\w+)\]/)[1];
                    },
                    objId: function (e) {
                        return (
                            e.__id ||
                                Object.defineProperty(e, "__id", {
                                    value: ++t,
                                }),
                            e.__id
                        );
                    },
                    clone: function (e) {
                        var t = n.util.type(e);
                        switch (t) {
                            case "Object":
                                var a = {};
                                for (var r in e)
                                    e.hasOwnProperty(r) &&
                                        (a[r] = n.util.clone(e[r]));
                                return a;
                            case "Array":
                                return (
                                    e.map &&
                                    e.map(function (e) {
                                        return n.util.clone(e);
                                    })
                                );
                        }
                        return e;
                    },
                },
                languages: {
                    extend: function (e, t) {
                        var a = n.util.clone(n.languages[e]);
                        for (var r in t) a[r] = t[r];
                        return a;
                    },
                    insertBefore: function (e, t, a, r) {
                        r = r || n.languages;
                        var i = r[e];
                        if (2 == arguments.length) {
                            a = arguments[1];
                            for (var l in a)
                                a.hasOwnProperty(l) && (i[l] = a[l]);
                            return i;
                        }
                        var o = {};
                        for (var s in i)
                            if (i.hasOwnProperty(s)) {
                                if (s == t)
                                    for (var l in a)
                                        a.hasOwnProperty(l) && (o[l] = a[l]);
                                o[s] = i[s];
                            }
                        return (
                            n.languages.DFS(n.languages, function (t, n) {
                                n === r[e] && t != e && (this[t] = o);
                            }),
                            (r[e] = o)
                        );
                    },
                    DFS: function (e, t, a, r) {
                        r = r || {};
                        for (var i in e)
                            e.hasOwnProperty(i) &&
                                (t.call(e, i, e[i], a || i),
                                "Object" !== n.util.type(e[i]) ||
                                r[n.util.objId(e[i])]
                                    ? "Array" !== n.util.type(e[i]) ||
                                      r[n.util.objId(e[i])] ||
                                      ((r[n.util.objId(e[i])] = !0),
                                      n.languages.DFS(e[i], t, i, r))
                                    : ((r[n.util.objId(e[i])] = !0),
                                      n.languages.DFS(e[i], t, null, r)));
                    },
                },
                plugins: {},
                highlightAll: function (e, t) {
                    var a = {
                        callback: t,
                        selector:
                            'code[class*="language-"], [class*="language-"] code, code[class*="lang-"], [class*="lang-"] code',
                    };
                    n.hooks.run("before-highlightall", a);
                    for (
                        var r,
                            i =
                                a.elements ||
                                document.querySelectorAll(a.selector),
                            l = 0;
                        (r = i[l++]);

                    )
                        n.highlightElement(r, e === !0, a.callback);
                },
                highlightElement: function (t, a, r) {
                    for (var i, l, o = t; o && !e.test(o.className); )
                        o = o.parentNode;
                    o &&
                        ((i = (o.className.match(e) || [
                            ,
                            "",
                        ])[1].toLowerCase()),
                        (l = n.languages[i])),
                        (t.className =
                            t.className.replace(e, "").replace(/\s+/g, " ") +
                            " language-" +
                            i),
                        (o = t.parentNode),
                        /pre/i.test(o.nodeName) &&
                            (o.className =
                                o.className
                                    .replace(e, "")
                                    .replace(/\s+/g, " ") +
                                " language-" +
                                i);
                    var s = t.textContent,
                        u = { element: t, language: i, grammar: l, code: s };
                    if (
                        (n.hooks.run("before-sanity-check", u),
                        !u.code || !u.grammar)
                    )
                        return n.hooks.run("complete", u), void 0;
                    if (
                        (n.hooks.run("before-highlight", u), a && _self.Worker)
                    ) {
                        var g = new Worker(n.filename);
                        (g.onmessage = function (e) {
                            (u.highlightedCode = e.data),
                                n.hooks.run("before-insert", u),
                                (u.element.innerHTML = u.highlightedCode),
                                r && r.call(u.element),
                                n.hooks.run("after-highlight", u),
                                n.hooks.run("complete", u);
                        }),
                            g.postMessage(
                                JSON.stringify({
                                    language: u.language,
                                    code: u.code,
                                    immediateClose: !0,
                                })
                            );
                    } else
                        (u.highlightedCode = n.highlight(
                            u.code,
                            u.grammar,
                            u.language
                        )),
                            n.hooks.run("before-insert", u),
                            (u.element.innerHTML = u.highlightedCode),
                            r && r.call(t),
                            n.hooks.run("after-highlight", u),
                            n.hooks.run("complete", u);
                },
                highlight: function (e, t, r) {
                    var i = n.tokenize(e, t);
                    return a.stringify(n.util.encode(i), r);
                },
                tokenize: function (e, t) {
                    var a = n.Token,
                        r = [e],
                        i = t.rest;
                    if (i) {
                        for (var l in i) t[l] = i[l];
                        delete t.rest;
                    }
                    e: for (var l in t)
                        if (t.hasOwnProperty(l) && t[l]) {
                            var o = t[l];
                            o = "Array" === n.util.type(o) ? o : [o];
                            for (var s = 0; s < o.length; ++s) {
                                var u = o[s],
                                    g = u.inside,
                                    c = !!u.lookbehind,
                                    h = !!u.greedy,
                                    f = 0,
                                    d = u.alias;
                                if (h && !u.pattern.global) {
                                    var p = u.pattern
                                        .toString()
                                        .match(/[imuy]*$/)[0];
                                    u.pattern = RegExp(
                                        u.pattern.source,
                                        p + "g"
                                    );
                                }
                                u = u.pattern || u;
                                for (
                                    var m = 0, y = 0;
                                    m < r.length;
                                    y += r[m].length, ++m
                                ) {
                                    var v = r[m];
                                    if (r.length > e.length) break e;
                                    if (!(v instanceof a)) {
                                        u.lastIndex = 0;
                                        var b = u.exec(v),
                                            k = 1;
                                        if (!b && h && m != r.length - 1) {
                                            if (
                                                ((u.lastIndex = y),
                                                (b = u.exec(e)),
                                                !b)
                                            )
                                                break;
                                            for (
                                                var w =
                                                        b.index +
                                                        (c ? b[1].length : 0),
                                                    _ = b.index + b[0].length,
                                                    A = m,
                                                    P = y,
                                                    x = r.length;
                                                x > A && _ > P;
                                                ++A
                                            )
                                                (P += r[A].length),
                                                    w >= P && (++m, (y = P));
                                            if (
                                                r[m] instanceof a ||
                                                r[A - 1].greedy
                                            )
                                                continue;
                                            (k = A - m),
                                                (v = e.slice(y, P)),
                                                (b.index -= y);
                                        }
                                        if (b) {
                                            c && (f = b[1].length);
                                            var w = b.index + f,
                                                b = b[0].slice(f),
                                                _ = w + b.length,
                                                O = v.slice(0, w),
                                                S = v.slice(_),
                                                j = [m, k];
                                            O && j.push(O);
                                            var N = new a(
                                                l,
                                                g ? n.tokenize(b, g) : b,
                                                d,
                                                b,
                                                h
                                            );
                                            j.push(N),
                                                S && j.push(S),
                                                Array.prototype.splice.apply(
                                                    r,
                                                    j
                                                );
                                        }
                                    }
                                }
                            }
                        }
                    return r;
                },
                hooks: {
                    all: {},
                    add: function (e, t) {
                        var a = n.hooks.all;
                        (a[e] = a[e] || []), a[e].push(t);
                    },
                    run: function (e, t) {
                        var a = n.hooks.all[e];
                        if (a && a.length)
                            for (var r, i = 0; (r = a[i++]); ) r(t);
                    },
                },
            }),
            a = (n.Token = function (e, t, n, a, r) {
                (this.type = e),
                    (this.content = t),
                    (this.alias = n),
                    (this.length = 0 | (a || "").length),
                    (this.greedy = !!r);
            });
        if (
            ((a.stringify = function (e, t, r) {
                if ("string" == typeof e) return e;
                if ("Array" === n.util.type(e))
                    return e
                        .map(function (n) {
                            return a.stringify(n, t, e);
                        })
                        .join("");
                var i = {
                    type: e.type,
                    content: a.stringify(e.content, t, r),
                    tag: "span",
                    classes: ["token", e.type],
                    attributes: {},
                    language: t,
                    parent: r,
                };
                if (
                    ("comment" == i.type && (i.attributes.spellcheck = "true"),
                    e.alias)
                ) {
                    var l =
                        "Array" === n.util.type(e.alias) ? e.alias : [e.alias];
                    Array.prototype.push.apply(i.classes, l);
                }
                n.hooks.run("wrap", i);
                var o = "";
                for (var s in i.attributes)
                    o +=
                        (o ? " " : "") +
                        s +
                        '="' +
                        (i.attributes[s] || "") +
                        '"';
                return (
                    "<" +
                    i.tag +
                    ' class="' +
                    i.classes.join(" ") +
                    '"' +
                    (o ? " " + o : "") +
                    ">" +
                    i.content +
                    "</" +
                    i.tag +
                    ">"
                );
            }),
            !_self.document)
        )
            return _self.addEventListener
                ? (_self.addEventListener(
                      "message",
                      function (e) {
                          var t = JSON.parse(e.data),
                              a = t.language,
                              r = t.code,
                              i = t.immediateClose;
                          _self.postMessage(n.highlight(r, n.languages[a], a)),
                              i && _self.close();
                      },
                      !1
                  ),
                  _self.Prism)
                : _self.Prism;
        var r =
            document.currentScript ||
            [].slice.call(document.getElementsByTagName("script")).pop();
        return (
            r &&
                ((n.filename = r.src),
                document.addEventListener &&
                    !r.hasAttribute("data-manual") &&
                    ("loading" !== document.readyState
                        ? window.requestAnimationFrame
                            ? window.requestAnimationFrame(n.highlightAll)
                            : window.setTimeout(n.highlightAll, 16)
                        : document.addEventListener(
                              "DOMContentLoaded",
                              n.highlightAll
                          ))),
            _self.Prism
        );
    })();
"undefined" != typeof module && module.exports && (module.exports = Prism),
    "undefined" != typeof global && (global.Prism = Prism);
Prism.languages.clike = {
    comment: [
        { pattern: /(^|[^\\])\/\*[\w\W]*?\*\//, lookbehind: !0 },
        { pattern: /(^|[^\\:])\/\/.*/, lookbehind: !0 },
    ],
    string: {
        pattern: /(["'])(\\(?:\r\n|[\s\S])|(?!\1)[^\\\r\n])*\1/,
        greedy: !0,
    },
    "class-name": {
        pattern:
            /((?:\b(?:class|interface|extends|implements|trait|instanceof|new)\s+)|(?:catch\s+\())[a-z0-9_\.\\]+/i,
        lookbehind: !0,
        inside: { punctuation: /(\.|\\)/ },
    },
    keyword:
        /\b(if|else|while|do|for|return|in|instanceof|function|new|try|throw|catch|finally|null|break|continue)\b/,
    boolean: /\b(true|false)\b/,
    function: /[a-z0-9_]+(?=\()/i,
    number: /\b-?(?:0x[\da-f]+|\d*\.?\d+(?:e[+-]?\d+)?)\b/i,
    operator: /--?|\+\+?|!=?=?|<=?|>=?|==?=?|&&?|\|\|?|\?|\*|\/|~|\^|%/,
    punctuation: /[{}[\];(),.:]/,
};
(Prism.languages.php = Prism.languages.extend("clike", {
    keyword:
        /\b(and|or|xor|array|as|break|case|cfunction|class|const|continue|declare|default|die|do|else|elseif|enddeclare|endfor|endforeach|endif|endswitch|endwhile|extends|for|foreach|function|include|include_once|global|if|new|return|static|switch|use|require|require_once|var|while|abstract|interface|public|implements|private|protected|parent|throw|null|echo|print|trait|namespace|final|yield|goto|instanceof|finally|try|catch)\b/i,
    constant: /\b[A-Z0-9_]{2,}\b/,
    comment: {
        pattern: /(^|[^\\])(?:\/\*[\w\W]*?\*\/|\/\/.*)/,
        lookbehind: !0,
        greedy: !0,
    },
})),
    Prism.languages.insertBefore("php", "class-name", {
        "shell-comment": {
            pattern: /(^|[^\\])#.*/,
            lookbehind: !0,
            alias: "comment",
        },
    }),
    Prism.languages.insertBefore("php", "keyword", {
        delimiter: /\?>|<\?(?:php)?/i,
        variable: /\$\w+\b/i,
        package: {
            pattern: /(\\|namespace\s+|use\s+)[\w\\]+/,
            lookbehind: !0,
            inside: { punctuation: /\\/ },
        },
    }),
    Prism.languages.insertBefore("php", "operator", {
        property: { pattern: /(->)[\w]+/, lookbehind: !0 },
    }),
    Prism.languages.markup &&
        (Prism.hooks.add("before-highlight", function (e) {
            "php" === e.language &&
                ((e.tokenStack = []),
                (e.backupCode = e.code),
                (e.code = e.code.replace(
                    /(?:<\?php|<\?)[\w\W]*?(?:\?>)/gi,
                    function (a) {
                        return (
                            e.tokenStack.push(a),
                            "{{{PHP" + e.tokenStack.length + "}}}"
                        );
                    }
                )));
        }),
        Prism.hooks.add("before-insert", function (e) {
            "php" === e.language &&
                ((e.code = e.backupCode), delete e.backupCode);
        }),
        Prism.hooks.add("after-highlight", function (e) {
            if ("php" === e.language) {
                for (var a, n = 0; (a = e.tokenStack[n]); n++)
                    e.highlightedCode = e.highlightedCode.replace(
                        "{{{PHP" + (n + 1) + "}}}",
                        Prism.highlight(a, e.grammar, "php").replace(
                            /\$/g,
                            "$$$$"
                        )
                    );
                e.element.innerHTML = e.highlightedCode;
            }
        }),
        Prism.hooks.add("wrap", function (e) {
            "php" === e.language &&
                "markup" === e.type &&
                (e.content = e.content.replace(
                    /(\{\{\{PHP[0-9]+\}\}\})/g,
                    '<span class="token php">$1</span>'
                ));
        }),
        Prism.languages.insertBefore("php", "comment", {
            markup: {
                pattern: /<[^?]\/?(.*?)>/,
                inside: Prism.languages.markup,
            },
            php: /\{\{\{PHP[0-9]+\}\}\}/,
        }));
        Prism.languages.sql = {
            comment: {
                pattern:
                    /(^|[^\\])(\/\*[\w\W]*?\*\/|((--)|(\/\/)).*?(\r?\n|$))/g,
                lookbehind: !0,
            },
            string: /("|')(\\?.)*?\1/g,
            keyword:
                /\b(ACTION|ADD|AFTER|ALGORITHM|ALTER|ANALYZE|APPLY|AS|AS|ASC|AUTHORIZATION|BACKUP|BDB|BEGIN|BERKELEYDB|BIGINT|BINARY|BIT|BLOB|BOOL|BOOLEAN|BREAK|BROWSE|BTREE|BULK|BY|CALL|CASCADE|CASCADED|CASE|CHAIN|CHAR VARYING|CHARACTER VARYING|CHECK|CHECKPOINT|CLOSE|CLUSTERED|COALESCE|COLUMN|COLUMNS|COMMENT|COMMIT|COMMITTED|COMPUTE|CONNECT|CONSISTENT|CONSTRAINT|CONTAINS|CONTAINSTABLE|CONTINUE|CONVERT|CREATE|CROSS|CURRENT|CURRENT_DATE|CURRENT_TIME|CURRENT_TIMESTAMP|CURRENT_USER|CURSOR|DATA|DATABASE|DATABASES|DATETIME|DBCC|DEALLOCATE|DEC|DECIMAL|DECLARE|DEFAULT|DEFINER|DELAYED|DELETE|DENY|DESC|DESCRIBE|DETERMINISTIC|DISABLE|DISCARD|DISK|DISTINCT|DISTINCTROW|DISTRIBUTED|DO|DOUBLE|DOUBLE PRECISION|DROP|DUMMY|DUMP|DUMPFILE|DUPLICATE KEY|ELSE|ENABLE|ENCLOSED BY|END|ENGINE|ENUM|ERRLVL|ERRORS|ESCAPE|ESCAPED BY|EXCEPT|EXEC|EXECUTE|EXIT|EXPLAIN|EXTENDED|FETCH|FIELDS|FILE|FILLFACTOR|FIRST|FIXED|FLOAT|FOLLOWING|FOR|FOR EACH ROW|FORCE|FOREIGN|FREETEXT|FREETEXTTABLE|FROM|FULL|FUNCTION|GEOMETRY|GEOMETRYCOLLECTION|GLOBAL|GOTO|GRANT|GROUP|HANDLER|HASH|HAVING|HOLDLOCK|IDENTITY|IDENTITY_INSERT|IDENTITYCOL|IF|IGNORE|IMPORT|INDEX|INFILE|INNER|INNODB|INOUT|INSERT|INT|INTEGER|INTERSECT|INTO|INVOKER|ISOLATION LEVEL|JOIN|KEY|KEYS|KILL|LANGUAGE SQL|LAST|LEFT|LIMIT|LINENO|LINES|LINESTRING|LOAD|LOCAL|LOCK|LONGBLOB|LONGTEXT|MATCH|MATCHED|MEDIUMBLOB|MEDIUMINT|MEDIUMTEXT|MERGE|MIDDLEINT|MODIFIES SQL DATA|MODIFY|MULTILINESTRING|MULTIPOINT|MULTIPOLYGON|NATIONAL|NATIONAL CHAR VARYING|NATIONAL CHARACTER|NATIONAL CHARACTER VARYING|NATIONAL VARCHAR|NATURAL|NCHAR|NCHAR VARCHAR|NEXT|NO|NO SQL|NOCHECK|NOCYCLE|NONCLUSTERED|NULLIF|NUMERIC|OF|OFF|OFFSETS|ON|OPEN|OPENDATASOURCE|OPENQUERY|OPENROWSET|OPTIMIZE|OPTION|OPTIONALLY|ORDER|OUT|OUTER|OUTFILE|OVER|PARTIAL|PARTITION|PERCENT|PIVOT|PLAN|POINT|POLYGON|PRECEDING|PRECISION|PREV|PRIMARY|PRINT|PRIVILEGES|PROC|PROCEDURE|PUBLIC|PURGE|QUICK|RAISERROR|READ|READS SQL DATA|READTEXT|REAL|RECONFIGURE|REFERENCES|RELEASE|RENAME|REPEATABLE|REPLICATION|REQUIRE|RESTORE|RESTRICT|RETURN|RETURNS|REVOKE|RIGHT|ROLLBACK|ROUTINE|ROWCOUNT|ROWGUIDCOL|ROWS?|RTREE|RULE|SAVE|SAVEPOINT|SCHEMA|SELECT|SERIAL|SERIALIZABLE|SESSION|SESSION_USER|SET|SETUSER|SHARE MODE|SHOW|SHUTDOWN|SIMPLE|SMALLINT|SNAPSHOT|SOME|SONAME|START|STARTING BY|STATISTICS|STATUS|STRIPED|SYSTEM_USER|TABLE|TABLES|TABLESPACE|TEMPORARY|TEMPTABLE|TERMINATED BY|TEXT|TEXTSIZE|THEN|TIMESTAMP|TINYBLOB|TINYINT|TINYTEXT|TO|TOP|TRAN|TRANSACTION|TRANSACTIONS|TRIGGER|TRUNCATE|TSEQUAL|TYPE|TYPES|UNBOUNDED|UNCOMMITTED|UNDEFINED|UNION|UNPIVOT|UPDATE|UPDATETEXT|USAGE|USE|USER|USING|VALUE|VALUES|VARBINARY|VARCHAR|VARCHARACTER|VARYING|VIEW|WAITFOR|WARNINGS|WHEN|WHERE|WHILE|WITH|WITH ROLLUP|WITHIN|WORK|WRITE|WRITETEXT)\b/gi,
            boolean: /\b(TRUE|FALSE|NULL)\b/gi,
            number: /\b-?(0x)?\d*\.?[\da-f]+\b/g,
            operator:
                /\b(ALL|AND|ANY|BETWEEN|EXISTS|IN|LIKE|NOT|OR|IS|UNIQUE|CHARACTER SET|COLLATE|DIV|OFFSET|REGEXP|RLIKE|SOUNDS LIKE|XOR)\b|[-+]{1}|!|=?&lt;|=?&gt;|={1}|(&amp;){1,2}|\|?\||\?|\*|\//gi,
            ignore: /&(lt|gt|amp);/gi,
            punctuation: /[;[\]()`,.]/g,
        };
!(function () {
    function e(e, t) {
        return Array.prototype.slice.call((t || document).querySelectorAll(e));
    }
    function t(e, t) {
        return (
            (t = " " + t + " "),
            (" " + e.className + " ").replace(/[\n\t]/g, " ").indexOf(t) > -1
        );
    }
    function n(e, n, i) {
        for (
            var o,
                a = n.replace(/\s+/g, "").split(","),
                l = +e.getAttribute("data-line-offset") || 0,
                d = r() ? parseInt : parseFloat,
                c = d(getComputedStyle(e).lineHeight),
                s = 0;
            (o = a[s++]);

        ) {
            o = o.split("-");
            var u = +o[0],
                m = +o[1] || u,
                h = document.createElement("div");
            (h.textContent = Array(m - u + 2).join(" \n")),
                h.setAttribute("aria-hidden", "true"),
                (h.className = (i || "") + " line-highlight"),
                t(e, "line-numbers") ||
                    (h.setAttribute("data-start", u),
                    m > u && h.setAttribute("data-end", m)),
                (h.style.top = (u - l - 1) * c + "px"),
                t(e, "line-numbers")
                    ? e.appendChild(h)
                    : (e.querySelector("code") || e).appendChild(h);
        }
    }
    function i() {
        var t = location.hash.slice(1);
        e(".temporary.line-highlight").forEach(function (e) {
            e.parentNode.removeChild(e);
        });
        var i = (t.match(/\.([\d,-]+)$/) || [, ""])[1];
        if (i && !document.getElementById(t)) {
            var r = t.slice(0, t.lastIndexOf(".")),
                o = document.getElementById(r);
            o &&
                (o.hasAttribute("data-line") || o.setAttribute("data-line", ""),
                n(o, i, "temporary "),
                document
                    .querySelector(".temporary.line-highlight")
                    .scrollIntoView());
        }
    }
    if (
        "undefined" != typeof self &&
        self.Prism &&
        self.document &&
        document.querySelector
    ) {
        var r = (function () {
                var e;
                return function () {
                    if ("undefined" == typeof e) {
                        var t = document.createElement("div");
                        (t.style.fontSize = "13px"),
                            (t.style.lineHeight = "1.5"),
                            (t.style.padding = 0),
                            (t.style.border = 0),
                            (t.innerHTML = "&nbsp;<br />&nbsp;"),
                            document.body.appendChild(t),
                            (e = 38 === t.offsetHeight),
                            document.body.removeChild(t);
                    }
                    return e;
                };
            })(),
            o = 0;
        Prism.hooks.add("complete", function (t) {
            var r = t.element.parentNode,
                a = r && r.getAttribute("data-line");
            r &&
                a &&
                /pre/i.test(r.nodeName) &&
                (clearTimeout(o),
                e(".line-highlight", r).forEach(function (e) {
                    e.parentNode.removeChild(e);
                }),
                n(r, a),
                (o = setTimeout(i, 1)));
        }),
            window.addEventListener && window.addEventListener("hashchange", i);
    }
})();
!(function () {
    "undefined" != typeof self &&
        self.Prism &&
        self.document &&
        Prism.hooks.add("complete", function (e) {
            if (e.code) {
                var t = e.element.parentNode,
                    s = /\s*\bline-numbers\b\s*/;
                if (
                    t &&
                    /pre/i.test(t.nodeName) &&
                    (s.test(t.className) || s.test(e.element.className)) &&
                    !e.element.querySelector(".line-numbers-rows")
                ) {
                    s.test(e.element.className) &&
                        (e.element.className = e.element.className.replace(
                            s,
                            ""
                        )),
                        s.test(t.className) || (t.className += " line-numbers");
                    var n,
                        a = e.code.match(/\n(?!$)/g),
                        l = a ? a.length + 1 : 1,
                        r = new Array(l + 1);
                    (r = r.join("<span></span>")),
                        (n = document.createElement("span")),
                        n.setAttribute("aria-hidden", "true"),
                        (n.className = "line-numbers-rows"),
                        (n.innerHTML = r),
                        t.hasAttribute("data-start") &&
                            (t.style.counterReset =
                                "linenumber " +
                                (parseInt(t.getAttribute("data-start"), 10) -
                                    1)),
                        e.element.appendChild(n);
                }
            }
        });
})();


