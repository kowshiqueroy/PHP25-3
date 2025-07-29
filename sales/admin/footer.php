</div>
    </div>

    <script>
        $(document).ready(function () {
            $("#menuToggle").click(function () {
                $("#menuBox").toggleClass("show");
            });
            $("#closeMenu").click(function () {
                $("#menuBox").removeClass("show");
            });
           
            $(".select2").select2();

            $(".select2edit").select2({
                tags: true
            });


        });
    </script>
</body>
</html>