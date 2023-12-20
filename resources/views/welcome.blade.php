<link type="text/css" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/ui-lightness/jquery-ui.css"
    rel="stylesheet" />
<link href='https://cdn.bootcss.com/chosen/1.5.1/chosen.min.css' rel='stylesheet' />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.2/chosen.jquery.min.js"></script>
<link id="bs-css" href="https://netdna.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" rel="stylesheet">
<link id="bsdp-css" href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker3.min.css"
    rel="stylesheet">
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
    <!-- Styles -->
    <style>
        html,
        body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }
        .full-height {
            height: 100vh;
        }
        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }
        .position-ref {
            position: relative;
        }
        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }
        .content {
            text-align: center;
            width: 100%;
        }
        .title {
            font-size: 84px;
        }
        .links>a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }
        .m-b-md {
            margin-bottom: 30px;
        }
        input {
            border: 0;
            outline: none;
            background-color: rgba(0, 0, 0, 0);
            text-align: center;
        }
        tbody {
            display: table-row-group;
            vertical-align: middle;
            border-color: inherit;
        }
        .submit-button {
            background-color: #2da44e;
            font-size: 0.3em;
            margin-top: 5%;
            text-align: center;
            color: #FFFFFF;
            font-weight: bold;
            border: 1px solid #8f7166;
            border-radius: 8px;
            width: 200px;
        }
        .img {
            margin-top: -18vw;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 50vw;
            height: 30vw;
            position: relative;
            background: center / contain url('/images/5月line表頭圖.png') no-repeat;
        }
        .context {
            margin-top: 41vw;
            background-color: white;
            text-align: center;
            position: absolute;
            border: 4px solid #e25371;
            border-radius: 2em;
            font-weight: 900;
            font-size: large;
            padding: 1vw;
        }
        input[type=submit] {
            color: white;
            background-color: #e25371;
            padding: 2px 39px 1px;
        }
        .btn {
            margin-top: 1vw;
            background-color: white;
            text-align: center;
            border: 1px solid #e25371;
            border-radius: 2em;
            font-weight: 900;
            font-size: large;
        }
    </style>
</head>

<body>
    <div class="flex-center position-ref full-height">
        <form method="GET" action="/home" id="present_form" style="display:inline;">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <div>
                <div class="img">
                    <div class="context">感謝大家今日的工作的辛勞，辛苦了！<br />
                        不為每個日子留下遺憾，記錄下今天工作的成果吧。<br /><input class="btn" type="submit" value="工作日誌 >>"></div>
                </div>
            </div>
        </form>
    </div>
</body>

</html>

<script>
    $(function() {
        $('.chosen').chosen({
            no_results_text: "没有找到结果！", //搜索无结果时显示的提示  
            search_contains: true, //关键字模糊搜索。设置为true，只要选项包含搜索词就会显示；设置为false，则要求从选项开头开始匹配
            allow_single_deselect: true, //单选下拉框是否允许取消选择。如果允许，选中选项会有一个x号可以删除选项
            disable_search: false, //禁用搜索。设置为true，则无法搜索选项。
            disable_search_threshold: 0, //当选项少等于于指定个数时禁用搜索。
            inherit_select_classes: true, //是否继承原下拉框的样式类，此处设为继承
            placeholder_text_single: '选择国家', //单选选择框的默认提示信息，当选项为空时会显示。如果原下拉框设置了data-placeholder，会覆盖这里的值。
            width: '100%', //设置chosen下拉框的宽度。即使原下拉框本身设置了宽度，也会被width覆盖。
            max_shown_results: 1000, //下拉框最大显示选项数量
            display_disabled_options: false,
            single_backstroke_delete: false, //false表示按两次删除键才能删除选项，true表示按一次删除键即可删除
            case_sensitive_search: false, //搜索大小写敏感。此处设为不敏感
            group_search: false, //选项组是否可搜。此处搜索不可搜
            include_group_label_in_selected: true //选中选项是否显示选项分组。false不显示，true显示。默认false。
        });

        $("#count_total tr").click(function() {
            console.log('click');
            var index = 1;
            var result_lists = $('.result_list');
            var list_table = $('.result_list:nth-child(' + index + ')');
            if (list_table.css('display') == 'none') {
                result_lists.css('display', 'none');
                list_table.css('display', 'table');
            } 
            else {
                list_table.css('display', 'none');
            }
        });
        $(".result_count tr").click(function() {
            var index = parseInt($(this).attr('order')) + 1;
            var result_lists = $('.result_list');
            var list_table = $('.result_list:nth-child(' + index + ')');
            if (list_table.css('display') == 'none') {
                result_lists.css('display', 'none');
                list_table.css('display', 'table');
            } 
            else {
                list_table.css('display', 'none');
            }
        });
    });

    $(document).ready(function() {
        //
        $('input[name="now_present"]:checkbox').click(function() {
            if ($('input[name="now_present"]:checkbox').attr('checked')) {
                $("#now_present").val("1");
                $("#present_time").attr("disabled", "disabled");
            } 
            else {
                $("#now_present").val("0");
                $("#present_time").removeAttr("disabled");
            }
        });
        $(".present_type").chosen({
            // 部分一致を許容する
            search_contains: true,
            width: "100%"
        });
        $(".item_select").chosen({
            // 部分一致を許容する
            search_contains: true,
            width: "100%"
        });
        $('#started_at').datepicker({
            viewDate: new Date()
        });
    });
</script>

<script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript">
    $(function() {
        $('#started_at').datepicker({
            // "setDate": new Date(),
            // "autoclose": true
        }).datepicker("setDate", "0");
    });
</script>
