@yield('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
<script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.14.0/jquery.validate.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

@if(config('services.google_maps_api_key'))
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps_api_key') }}&libraries=places&callback=initCityAutocomplete">
</script>
<script>
    // Google Map Autocomplete for City (if needed)
    function initCityAutocomplete() {
        // Initialize Google Maps autocomplete if needed
        // This is a placeholder - implement based on your needs
    }
    window.onload = initCityAutocomplete;
</script>
@endif

<script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>

<script>
    jQuery(document).ready(function($) {
        // Image preview functionality
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview img').attr('src', e.target.result);
                    $('#imagePreviewCompany img').attr('src', e.target.result);
                    $('#imagePreview img').hide();
                    $('#imagePreviewCompany img').hide();
                    $('#imagePreview img').fadeIn(650);
                    $('#imagePreviewCompany img').fadeIn(650);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#imageUpload").change(function() {
            readURL(this);
        });
        $("#imageUploadCompany").change(function() {
            readURL(this);
        });

        $('.personalProfilePicBtn').on('click', function(e) {
            e.preventDefault();
            $('#imageUpload').trigger('click');
        });

        $('.companyProfilePicBtn').on('click', function(e) {
            e.preventDefault();
            $('#imageUploadCompany').trigger('click');
        });
    });
</script>

@auth
<script>
    // Clear token on logout
    document.querySelector(".logoutBtn")?.addEventListener("click", function() {
        localStorage.setItem("sanctum-token", "");
    });
</script>
@endauth

</body>

</html>
