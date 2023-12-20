<!DOCTYPE html>
<html>

<head>
    <title>未授權</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@latest"></script>
</head>

<body>
</body>

@if (isset($data))
    <script>
        data = Object.values(@json($data)),

        SwalTitle = data[0]
        SwalIcon  = data[2]

        timeSeconds = 3;
        // 顯示 SweetAlert 彈出視窗
        const timerInterval = setInterval(function() {
            timeSeconds--;
            Swal.update({
                title: SwalTitle,
                text: `將在 ${timeSeconds} 秒後返回google登入主頁`,
                timerProgressBar: true,
            });
            if (timeSeconds <= 0) {
                clearInterval(timerInterval);
                window.location.href = '/';
            }
        }, 1000);

        Swal.fire({
                title: SwalTitle,
                text: `將在 ${timeSeconds} 秒後返回google登入主頁`,
                icon: SwalIcon,
                showCloseButton: true,
                timer: timeSeconds * 1000,
                timerProgressBar: true,
            })
            .then((result) => {
                if (result.isConfirmed || result.dismiss || Swal.DismissReason.close) {
                    window.location.href = '/';
                }
            });
    </script>
@endif

</html>
