<?php
/* Template Name: Submit Story */
get_header();
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <?php 
                $post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
                // if (isset($_GET['competition_id']) && is_numeric($_GET['competition_id'])) {
                //     $competition_id = intval($_GET['competition_id']);
                //     $competition_title = get_the_title($competition_id);
                // } else {
                //     $competition_id = 0;
                //     $competition_title = 'Unknown Competition';
                // }

                // $category_id = intval($_GET['category_id']);

                if ($post_id) {
                    $post = get_post($post_id);
                    if ($post) {
                        $title = esc_attr($post->post_title);
                        $content = esc_textarea($post->post_content);
                    } else {
                        $title = '';
                        $content = '';
                    }
                } else {
                    $title = '';
                    $content = '';
                }

                $expiry_date = get_post_meta(get_the_ID(), '_competition_expiry_date', true);
                $competition_closed = $expiry_date && strtotime($expiry_date) < time();
            ?>

            <!-- <h5 class="text-primary-color fw-bold">Create Story for: <?php echo esc_html($competition_title); ?></h5> -->

            <div class="shadow-lg rounded my-4 p-4">
                <?php
                    if (is_user_logged_in()) :
                        $image_url = '';
                        if ($post_id && has_post_thumbnail($post_id)) {
                            $image_url = get_the_post_thumbnail_url($post_id, 'medium');
                        }
                ?>
                    <div id="competition-warning" class="alert alert-warning d-none mt-3">
                        This competition is currently closed for submissions.
                    </div>

                    <form id="competition-post-form" class="p-0 p-xl-4">
                        <input type="hidden" id="post-id" value="<?php echo $post_id; ?>">
                        <!-- <input type="hidden" id="competition-id" value="<?php echo $competition_id; ?>"> -->
                        <input type="hidden" id="category-id" value="">

                        <div class="row mb-4">
                            <label for="competition-id" class="col-12 col-xl-1 col-form-label">Competition <span class="text-danger">*</span></label>
                            <div class="col-sm-6 col-xl-4">
                                <select id="competition-id" class="form-select">
                                    <option value="">Select Competition</option>
                                    <?php
                                    $competitions = get_posts([
                                        'post_type' => 'competition',
                                        'posts_per_page' => -1,
                                        'orderby' => 'title',
                                        'order' => 'ASC',
                                        'post_status' => 'publish',
                                    ]);

                                    foreach ($competitions as $competition) {
                                        $selected = ($competition_id == $competition->ID) ? 'selected' : '';
                                        echo '<option value="' . esc_attr($competition->ID) . '" ' . $selected . '>' . esc_html($competition->post_title) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <label for="title" class="col-12 col-xl-1 col-form-label">Title <span class="text-danger">*</span></label>
                            <div class="col-sm-6 col-xl-4">
                                <input type="text" class="form-control" id="post-title" placeholder="Title" value="<?php echo $title; ?>">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <label for="post-image" class="col-12 col-xl-1 col-form-label">Image <span class="text-danger">*</span></label>
                            <div class="col-sm-6 col-xl-4">
                                <input type="file" class="form-control" id="post-image" name="post_image" accept="image/*">
                                <div class="mt-2">
                                    <img id="image-preview" src="<?php echo esc_url($image_url); ?>" alt="Image preview" class="img-fluid rounded border" style="max-height: 150px; <?php echo $image_url ? '' : 'display: none;'; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <label for="post-content" class="col-12 col-xl-1 col-form-label">Description <span class="text-danger">*</span></label>
                            <div class="col-12 col-xl-11">
                                <textarea id="post-content" class="form-control" placeholder="Description" rows="10"><?php echo $content; ?></textarea>
                            </div>
                        </div>

                        <div class="row mb-4 align-items-center">
                            <div class="col-sm-9 offset-xl-1">
                                <?php if (!$competition_closed): ?>
                                    <button type="submit" id="competitionSubmit" class="btn btn-primary btn-sm pt-2">
                                        <?php if ($post_id): ?>
                                            <i class="fa-solid fa-floppy-disk"></i>
                                            &nbsp; Update
                                        <?php else: ?>
                                            <i class="fa-solid fa-plus"></i>
                                            &nbsp; Create
                                        <?php endif; ?>
                                    </button>
                                <?php endif; ?>
                                <?php
                                    $competitionBackUrl = get_permalink(get_page_by_path('competition'));
                                ?>
                                <button class="btn btn-secondary btn-sm ms-3 pt-2" onclick="window.location.href='<?php echo esc_url($competitionBackUrl); ?>'">
                                    <i class="fa-solid fa-arrow-left"></i>&nbsp; Back
                                </button>
                            </div>
                        </div>
                    </form>
                    <div id="post-response"></div>
                <?php else : ?>
                    <p>Please <a href="<?php echo wp_login_url(get_permalink()); ?>">log in</a> to submit a story.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>

<script>
jQuery(document).ready(function($) {
    // let editor;

    // ClassicEditor
    //     .create(document.querySelector('#post-content'), {
    //         toolbar: [
    //             'heading', '|',
    //             'bold', 'italic', 'underline', 'strikethrough', 'code', 'subscript', 'superscript', '|',
    //             'bulletedList', 'numberedList', 'todoList', '|',
    //             'alignment', 'outdent', 'indent', '|',
    //             'link', 'blockQuote', 'imageUpload', 'insertTable', 'mediaEmbed', '|',
    //             'undo', 'redo', 'emoji'
    //         ]
    //     })
    //     .then(newEditor => {
    //         editor = newEditor;
    //     })
    //     .catch(error => {
    //         console.error('CKEditor error:', error);
    //     });
    
    $("#post-content").trumbowyg({
        btns: [
            ['formatting'],
            ['fontsize'],
            ["bold", "italic", "underline"],
            ['unorderedList', 'orderedList'],
            ["emoji"]
        ],
        autogrow: true
    });

    // Start checking for API availability
    // loadTamilTyping();

    document.getElementById('competition-id').addEventListener('change', function () {
        const competitionId = this.value;

        if (!competitionId) return;

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'get_competition_category',
                competition_id: competitionId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('category-id').value = data.data.category_id;
            } else {
                console.warn('No category found for this competition.');
            }
        })
        .catch(error => console.error('Error fetching category:', error));
    });

    $('#post-image').on('change', function() {
        const file = this.files[0];
        const preview = $('#image-preview');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        } else {
            preview.hide();
        }
    });

    $('#competition-post-form').submit(function(e) {
        e.preventDefault();

        let isValid = true;
        let title = $('#post-title').val().trim();
        let content = $('#post-content').val().trim();
        let image = $('#post-image')[0].files[0];
        // let content = editor.getData().trim();

        $('.error-message').remove();

        if (title === '') {
            isValid = false;
            $('#post-title').after('<small class="text-danger error-message">Title is required.</small>');
        }

        if (content === '') {
            isValid = false;
            $('#post-content').after('<small class="text-danger error-message">Story is required.</small>');
        }

        if (!image && $('#post-id').val() === '0') {
            isValid = false;
            $('#post-image').after('<small class="text-danger error-message">Image is required.</small>');
        }

        if (!isValid) return;

        const formData = new FormData();
        formData.append('action', 'submit_competition_post');
        formData.append('post_title', title);
        formData.append('post_content', content);
        formData.append('category_id', $('#category-id').val());
        formData.append('competition_id', $('#competition-id').val());
        formData.append('post_id', $('#post-id').val());
        if (image) {
            formData.append('post_image', image);
        }

        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.redirect_url;
                } else {
                    $('#post-response').text(response.data);
                }
            }
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const dropdown = document.getElementById("competition-id");
    const warningBox = document.getElementById("competition-warning");
    const titleInput = document.getElementById("post-title");
    const imageInput = document.getElementById("post-image");
    // const descriptionInput = document.getElementById("post-content");
    const createButton = document.getElementById("competitionSubmit");
    const descriptionInput = document.querySelector(".trumbowyg-editor");

    dropdown.addEventListener("change", function () {
        const competitionId = this.value;

        if (!competitionId) return;

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'check_competition_expiry',
                competition_id: competitionId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.expired) {
                warningBox.classList.remove('d-none');
                titleInput.disabled = true;
                imageInput.disabled = true;
                descriptionInput.disabled = true;
                createButton.disabled = true;
            } else {
                warningBox.classList.add('d-none');
                titleInput.disabled = false;
                imageInput.disabled = false;
                descriptionInput.disabled = false;
                createButton.disabled = false;
            }
        });
    });
});
</script>
