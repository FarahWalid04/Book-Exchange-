  <!-- The main tag from your content pages is closed right before this footer starts -->
  <footer class="site-footer">
      <div class="container footer-container">
          <!-- ... all your footer link sections ... -->
      </div>
      <div class="footer-bottom">
          <div class="container bottom-container">
              <p>&copy; <?php echo date("Y"); ?> Book Exchange. All Rights Reserved.</p>
          </div>
      </div>
  </footer>

<!-- ======================================================== -->
<!-- == JAVASCRIPT FOR PROFILE PHOTO UPLOAD                == -->
<!-- ======================================================== -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Find the elements on the page by their unique IDs.
    const changePhotoButton = document.getElementById('change-photo-btn');
    const photoUploadForm = document.getElementById('photo-upload-form');
    const photoInput = document.getElementById('profile-image-input');

    // This 'if' statement prevents errors on pages other than the profile page.
    if (changePhotoButton && photoInput && photoUploadForm) {
        
        // Listen for a click on the visible "Change Photo" button.
        changePhotoButton.addEventListener('click', function() {
            // When clicked, trigger a click on the invisible file input.
            // This is what opens the file selection dialog for the user.
            photoInput.click();
        });

        // Listen for a change in the invisible file input (i.e., when the user selects a file).
        photoInput.addEventListener('change', function() {
            // If the user has selected a file...
            if (photoInput.files.length > 0) {
                // ...automatically submit the hidden form.
                // The form's action attribute sends the file to 'upload-profile-photo.php'.
                photoUploadForm.submit();
            }
        });
    }
});
</script>

</body>
</html>

