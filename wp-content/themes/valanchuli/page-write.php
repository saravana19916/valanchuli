<?php
/**
 * Template Name: Write Story Page
 */
get_header(); ?>

<?php
$today = date('Y-m-d');
$postId = '';
if ( isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {
    $postId = (int) $_GET['id'];
}
?>

<div class="container mt-5">
	<div class="row">
		<h5 class="text-center text-primary-color fw-bold"> எழுத </h5>
	</div>

	<?php if ( ! is_user_logged_in() ) {
		$currentUrl = get_permalink();
		$loginPage = get_page_by_path('login');
		$loginUrl = get_permalink($loginPage);

		$loginUrlWithRedirect = add_query_arg('redirect_to', urlencode($currentUrl), $loginUrl);
	?>
		<div class="alert alert-warning text-center w-50 mx-auto mt-3" role="alert" id="draftAlert">
			தயவு செய்து உள்நுழையவும். This page is restricted. Please 
			<a href="<?php echo esc_url($loginUrlWithRedirect); ?>" class="alert-link">Login / Register</a> to view this page.
		</div>
	<?php } else { ?>

		<div id="save-result" class="mt-3"></div>

		<form id="write-story-form" enctype="multipart/form-data">
			<!-- Step 1 -->
			<div id="step-1">
				<div class="row d-flex justify-content-center align-items-center p-2 p-lg-5">
					<div class="col-lg-5 col-12">
						<div class="d-flex justify-content-center align-items-center">
							<img src="<?php echo get_template_directory_uri() . '/images/story-write.png'; ?>" alt="Login Image" style="width: 60%;" />
						</div>
					</div>
					<div class="col-lg-7 col-12 mt-3 mt-lg-0 p-3 p-lg-5 bg-white">
						<input type="text" id="editPostId" class="d-none" value="<?php echo $postId; ?>">

						<?php if (isset($_GET['from']) && $_GET['from'] === 'competition') { ?>
							<div class="mb-4" id="competitionDropdown">
								<input type="text" class="d-none" id="story-from-competition" value="true">

								<label class="form-label">போட்டிகள் <span style="color: red;">*</span></label>
								<select class="form-select login-form-group story-competition" id="story-competition">
									<option value="">-- select --</option>
									<?php
										$competitions = get_posts([
											'post_type' => 'competition',
											'posts_per_page' => -1,
											'post_status' => 'publish',
											'orderby' => 'title',
											'order' => 'ASC',
											'meta_query'     => [
												'relation' => 'AND',
												[
													'key'     => '_competition_start_date',
													'value'   => $today,
													'compare' => '<=',
													'type'    => 'DATE',
												],
												[
													'key'     => '_competition_end_date',
													'value'   => $today,
													'compare' => '>=',
													'type'    => 'DATE',
												],
											],
										]);
									?>

									<?php foreach ($competitions as $competition) : ?>
										<option value="<?php echo esc_attr($competition->ID); ?>">
											<?php echo esc_html($competition->post_title); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						<?php } ?>

						<?php

							if (isset($_GET['from']) && $_GET['from'] === 'competition') {
								$static_series = [];
							} else {
								$static_series = ['தொடர்கதை அல்ல'];
							}

							$current_user_id = get_current_user_id();

							$series_terms = get_terms([
								'taxonomy'   => 'series',
								'hide_empty' => false,
							]);

							$filtered_series = array_filter($series_terms, function ($term) use ($current_user_id) {
								if ($term->name === 'தொடர்கதை அல்ல') {
									return false;
								}

								$query = new WP_Query([
									'post_type'      => 'post',
									'posts_per_page' => 1,
									'post_status'    => 'any',
									'author'         => $current_user_id,
									'tax_query'      => [
										[
											'taxonomy' => 'series',
											'field'    => 'term_id',
											'terms'    => $term->term_id,
										],
									],
								]);

								return $query->have_posts();
							});

							$dynamic_series = [];
							foreach ($filtered_series as $term) {
								$dynamic_series[] = $term->name;
							}

						?>

						<div class="mb-4 dropdown">
							<label for="category_dropdown_input" class="form-label">தொடர்கதை <span style="color: red;">*</span></label>

							<input type="text" readonly class="form-control dropdown-toggle form-select login-form-group story-series_input" id="story-series"
								name="story-series" data-bs-toggle="dropdown">

							<ul class="dropdown-menu w-100 p-2" id="category_dropdown">
								<li>
									<input type="text" class="form-control mb-2 tamil-suggestion-input" id="series_input"
										placeholder="Type to create or search...">
								</li>
								<div id="category_list"></div>
							</ul>

							<p class="my-2 fs-12px" style="color: gray;"><i>உங்கள் படைப்பு ஏதேனும் தொடர்கதையாக  இருந்தால் மட்டும் தேர்ந்தெடுக்கவும்.</i></p>
						</div>

						<div class="mb-4">
							<label class="form-label">தலைப்பு <span style="color: red;">*</span></label>
							<input type="text" class="form-control tamilwriter login-form-group story-title tamil-suggestion-input" id="story-title">
							<p class="tamil-suggestion-box mt-2" data-suggestion-for="story-title" style="display: none;"></p>
						</div>

						<!-- <div class="mb-4 dropdown">
    <label for="category_dropdown_input" class="form-label">தொடர்கதை <span style="color: red;">*</span></label>

    <input type="text" readonly class="form-control dropdown-toggle form-select login-form-group story-series_input" id="story-series"
        name="story-series" data-bs-toggle="dropdown" value="தொடர்கதை அல்ல">

    <ul class="dropdown-menu w-100 p-2" id="category_dropdown" style="display: none;">
        <li>
            <input type="text" class="form-control mb-2 tamil-suggestion-input" id="series_input"
                placeholder="Type to create or search...">
        </li>
        <div id="category_list"></div>
    </ul>

    <div id="tamil_suggestion_popup" class="tamil-suggestion-popup" style="display: none; position: absolute; background: white; border: 1px solid #ccc; padding: 10px; width: 300px; z-index: 1000; border-radius: 5px;">
        <div id="suggestion_list"></div>
    </div>

    <p class="my-2 fs-12px" style="color: gray;">
        <i>உங்கள் படைப்பு ஏதேனும் தொடர்கதையாக இருந்தால் மட்டும் தேர்ந்தெடுக்கவும்.</i>
    </p>
</div> -->


						<div class="mb-4" id="categoryDropdown">
							<label class="form-label">வகை <span style="color: red;">*</span></label>
							<select class="form-select login-form-group story-category" id="story-category">
								<option value="">-- select --</option>
								<?php
								$categories = get_categories([
									'taxonomy' => 'category',
									'hide_empty' => false,
									'exclude' => get_cat_ID('Uncategorized'),
								]);
								foreach ($categories as $cat) {
									echo '<option value="' . esc_attr($cat->term_id) . '">' . esc_html($cat->name) . '</option>';
								}
								?>
							</select>
							<p class="my-2 fs-12px" style="color: gray;"><i>இந்த படைப்பு எந்த வகையை சேர்ந்தது என்பதை தேர்ந்தெடுக்கவும் (உதாரணம்: காதல், குடும்பம், நகைச்சுவை, தொடர்கதை)</i></p>
						</div>

						<div class="mb-4 d-none" id="divisionDropdown">
							<label class="form-label">பிரிவுகள்</label>
							<?php
								$divisions = get_terms(array(
									'taxonomy'   => 'division',
									'hide_empty' => false, // show even if no posts are assigned
								));

								if (!empty($divisions) && !is_wp_error($divisions)) :
							?>
									<select class="form-select login-form-group" id="story-division" name="sotry-division">
										<option value="">Select Division</option>
										<?php foreach ($divisions as $division) : ?>
											<option value="<?php echo esc_attr($division->term_id); ?>">
												<?php echo esc_html($division->name); ?>
											</option>
										<?php endforeach; ?>
									</select>
								<?php endif; ?>
						</div>

						<input type="text" class="form-control mb-2 d-none" id="seriesFirst">
						<div class="mb-4 d-none" id="descriptionSection">
							<label class="form-label">Description</label>
							<textarea class="form-control text-primary-color login-form-group tamil-suggestion-input" id="story-description" name="story-description" rows="4" placeholder="Short description"></textarea>
							<p class="tamil-suggestion-box mt-2" data-suggestion-for="story-description" style="display:none;"></p>
						</div>

						<!-- Image Upload -->
						<div class="mb-4" id="imageSection">
							<label class="form-label">படம்</label>
							<input type="file" class="form-control login-form-group" id="story-image" accept="image/*">
						</div>

						<button type="button" class="btn btn-secondary" id="next-step"><i class="fa-solid fa-arrow-right"></i>&nbsp;
							அடுத்தது</button>
						<button type="submit" id="step1Submit" class="btn btn-primary me-2 d-none"><i class="fa-solid fa-floppy-disk"></i>&nbsp;
						சமர்ப்பிக்க</button>
					</div>					
				</div>
			</div>

			<!-- Step 2 -->
			<div id="step-2" class="mb-3" style="display: none;">
				<div class="d-flex justify-content-center">
					<div class="alert alert-warning d-none text-center w-50" role="alert" id="draftAlert">
						Story saved as draft
					</div>
				</div>

				<div class="my-3">
					<label class="form-label">
						படைப்பை சேர்க்கவும் <span style="color: red;">*</span>
						<span class="spinner-border text-success ms-2 align-middle" role="status" id="content-loader" style="display: none; width: 1rem; height: 1rem;" aria-hidden="true"></span>
					</label>
					<textarea id="story-content" class="form-control tamilwriter story-content" rows="6"></textarea>
					<ul id="tanglishSuggestions" 
						style="position:absolute; z-index:9999; background:#fff; border:1px solid #ccc; list-style:none; padding:0; margin:0; display:none; min-width:120px;">
					</ul>
					<p class="mt-2 d-block text-primary-color fw-bold">
						Word Count:
						<span class="badge bg-primary-color text-highlight-color fw-bold fs-14px p-2" id="word-count">0</span>
						<span class="spinner-border text-success ms-2 align-middle" role="status" id="content-loader-2" style="display: none; width: 1rem; height: 1rem;" aria-hidden="true"></span>
					</p>

				</div>


				<button type="button" class="btn btn-secondary me-2" id="prev-step"><i class="fa-solid fa-arrow-left"></i>&nbsp;
					முந்தையது</button>
				<button type="submit" class="btn btn-primary me-2"><i class="fa-solid fa-floppy-disk"></i>&nbsp;
					சமர்ப்பிக்க</button>
				<button type="button" class="btn btn-primary" id="saveDraft"><i class="fa-solid fa-floppy-disk"></i>&nbsp;
					Save Draft</button>
			</div>
		</form>
	<?php } ?>
</div>

<?php get_footer(); ?>

<script>
    var my_ajax_object = {
        ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
        nonce: '<?php echo wp_create_nonce('trumbowyg_upload_nonce'); ?>'
    };
</script>
<script>

	jQuery(document).ready(function ($) {
		$('#next-step').on('click', function () {
			const storyCompetition = document.getElementById('story-competition')?.value;
			const isCompetitionPage = document.getElementById('story-from-competition')?.value;
			const title = document.getElementById('story-title').value;
			const category = document.getElementById('story-category').value;
			const series = document.getElementById('story-series').value;

			$('.error-message').remove();

			// Initialize empty array
			let errors = [];

			// Validation checks
			if (isCompetitionPage == 'true' && storyCompetition === '') {
				errors.push({ field: 'competition', message: 'போட்டிகள் is required.' });
			}

			if (title === '') {
			errors.push({ field: 'title', message: 'தலைப்பு is required.' });
			}

			if (!document.getElementById('categoryDropdown').classList.contains('d-none') && category === '') {
			errors.push({ field: 'category', message: 'வகை is required.' });
			}

			if (series === '') {
			errors.push({ field: 'series', message: 'தொடர்கதை is required.' });
			}

			// Show errors
			$.each(errors, function (index, error) {
				$('.story-' + error.field).after(
					'<p class="text-danger error-message mt-2 small">' + error.message + '</p>'
				);
			});

			if (errors.length == 0) {
				$('#step-1').hide();
				$('#step-2').show();

				if (!$('#story-content').data('trumbowyg')) {
					$("#story-content").trumbowyg({
						btns: [
							['formatting'],
							['fontsize'],
							["bold", "italic", "underline"],
							['justifyLeft', 'justifyCenter', 'justifyRight'],
							['unorderedList', 'orderedList'],
							["insertImage", "upload"],
							["emoji"]
						],
						autogrow: true,
						plugins: {
        upload: {
            serverPath: '/wp-admin/admin-ajax.php',
            fileFieldName: 'file',
            data: { action: 'trumbowyg_upload' },
            urlPropertyName: 'url', // JSON response property with file URL
            success: function(data) {
                // optional: editor-ku manual insert panna
                if (data && data.url) {
                    $('#story-content').trumbowyg('execCmd', {
                        cmd: 'insertImage',
                        param: data.url
                    });
                }
            }
        }
    }
					}).on('tbwchange tbwinit', updateWordCount);

					const suggestionBox = $('#tanglishSuggestions');
					let editor = $('#story-content').next('.trumbowyg-box').find('.trumbowyg-editor')[0];

					if (!editor) {
						editor = document.querySelector('.trumbowyg-editor');
					}

					if (!editor) {
						console.error('Editor not found');
						return;
					}

					let activeRequests = 0;

					editor.addEventListener('input', () => {
						const sel = window.getSelection();
						if (!sel.rangeCount) {
						suggestionBox.hide();
						return;
						}

						const range = sel.getRangeAt(0);
						if (range.startContainer.nodeType !== Node.TEXT_NODE) {
						suggestionBox.hide();
						return;
						}

						const text = range.startContainer.textContent;
						const offset = range.startOffset;

						const textBeforeCursor = text.slice(0, offset);
						const lastWord = textBeforeCursor.trim().split(/\s+/).pop();

						if (!lastWord) {
							suggestionBox.hide();
							return;
						}

						$('#content-loader').show();
						$('#content-loader-2').show();

						$('#saveDraft, .btn[type="submit"], #prev-step').prop('disabled', true);

						activeRequests++;

						fetch(`https://inputtools.google.com/request?text=${encodeURIComponent(lastWord)}&itc=ta-t-i0-und&num=5`)
						.then(res => res.json())
						.then(data => {
							if (data[0] === "SUCCESS") {
								const suggestions = data[1][0][1];
								showSuggestions(suggestions, range);
							} else {
								suggestionBox.hide();
							}
						})
						.catch(() => suggestionBox.hide())
						.finally(() => {
							activeRequests--;

							if (activeRequests === 0) {
								$('#saveDraft, .btn[type="submit"], #prev-step').prop('disabled', false);
								$('#content-loader').hide();
								$('#content-loader-2').hide();
							}
						});
					});

					function showSuggestions(suggestions, range) {
						suggestionBox.empty();

						suggestions.forEach(s => {
						$('<li>')
							.text(s)
							.css({'padding': '6px 10px', 'cursor': 'pointer'})
							.on('click', () => {
							replaceLastWord(s);
							suggestionBox.hide();
							})
							.appendTo(suggestionBox);
						});

						const rect = range.getBoundingClientRect();

						suggestionBox.css({
						top: rect.bottom + window.scrollY + 'px',
						left: rect.left + window.scrollX + 'px',
						display: 'block'
						});
					}

					function replaceLastWord(newWord) {
						const sel = window.getSelection();
						if (!sel.rangeCount) return;

						const range = sel.getRangeAt(0);
						const node = range.startContainer;
						const text = node.textContent;
						const offset = range.startOffset;

						const textBeforeCursor = text.slice(0, offset);
						const lastSpaceIndex = textBeforeCursor.lastIndexOf(' ');
						const start = lastSpaceIndex + 1;

						const newText = text.slice(0, start) + newWord + ' ' + text.slice(offset);
						node.textContent = newText;

						const newOffset = start + newWord.length + 1;
						const newRange = document.createRange();
						newRange.setStart(node, newOffset);
						newRange.collapse(true);

						sel.removeAllRanges();
						sel.addRange(newRange);

						editor.focus();
					}
				}

				// Optional: Adjust editor height
				$('.trumbowyg-editor').css({
					height: '35rem',
					overflow: 'auto'
				});
			}
		});

		function updateWordCount() {
			const content = $('#story-content').trumbowyg('html');
			const textOnly = $('<div>').html(content).text();
			const wordCount = textOnly.trim().split(/\s+/).filter(word => word.length > 0).length;
			$('#word-count').text(wordCount);

			// setTimeout(() => {
			// 	$('#content-loader').hide();
			// 	$('#content-loader-2').hide();
			// 	$('#saveDraft, .btn[type="submit"], #prev-step').prop('disabled', false);
			// }, 300);
		}

		$('#prev-step').on('click', function () {
			$('#step-2').hide();
			$('#step-1').show();
		});

		$('#story-content').on('tbwchange', function () {
			startAutoSave();
		});

		let autoSaveInterval = null;

		function startAutoSave() {
			autoSaveInterval = setInterval(function () {
				autoSaveDraft(true);
			}, 2 * 60 * 1000);
		}

		startAutoSave();

		$('#saveDraft').click(function() {
			autoSaveDraft(false);
		});
	});

	function updateList(filter = '') {
		const categoryInput = document.getElementById('series_input');
		const categoryList = document.getElementById('category_list');

		const staticSeries = <?php echo json_encode($static_series, JSON_UNESCAPED_UNICODE); ?>;
		const dynamicSeries = <?php echo json_encode($dynamic_series, JSON_UNESCAPED_UNICODE); ?>;

		const labelMap = {
			...staticSeries,
			...dynamicSeries
		};

		const categories = Object.values(staticSeries).concat(Object.values(dynamicSeries));

			const selectedInput = document.getElementById('story-series');

			categoryList.innerHTML = '';
			const filtered = categories.filter(cat => cat.toLowerCase().includes(filter.toLowerCase()));
			
			if (filtered.length > 0) {
				const storyCategory = document.getElementById('story-category');

				if (selectedInput.value != 'தொடர்கதை அல்ல') {
					const seriesFirst = document.getElementById("seriesFirst").value;

					if (seriesFirst || seriesFirst == 'true') {
						storyCategory.disabled = true;
					} else {
						document.getElementById("descriptionSection").classList.add("d-none");
						document.getElementById("categoryDropdown").classList.add("d-none");
						document.getElementById("divisionDropdown").classList.add("d-none");
						document.getElementById("imageSection").classList.add("d-none");
						document.getElementById("step1Submit").classList.add("d-none");
						document.getElementById("next-step").classList.remove("d-none");
					}
					
				} else {
					storyCategory.disabled = false;
				}

				filtered.forEach(cat => {
					const item = document.createElement('li');
					item.innerHTML = `<a href="#" class="dropdown-item">${cat}</a>`;
					item.querySelector('a').addEventListener('click', function (e) {
						e.preventDefault();
						selectedInput.value = cat;
						categoryInput.value = '';
						selectedInput.focus();
					});
					categoryList.appendChild(item);
				});
			} else {
				const item = document.createElement('li');
				item.innerHTML = `<a href="#" class="dropdown-item text-primary">Add "${filter}"</a>`;

				const suggestionP = document.createElement('p');
				suggestionP.className = 'tamil-suggestion-box mt-2';
				suggestionP.dataset.suggestionFor = 'series_input';
				suggestionP.style.display = 'none';
				item.appendChild(suggestionP);

				item.querySelector('a').addEventListener('click', function (e) {
					e.preventDefault();
					selectedInput.value = filter;
					categoryInput.value = '';
					selectedInput.focus();
					const dropdownMenu = document.getElementById('category_dropdown');
    				dropdownMenu.classList.remove('show');
				});

				categoryList.appendChild(item);

				if (filter.length > 1) {
					fetch(`https://inputtools.google.com/request?text=${encodeURIComponent(filter)}&itc=ta-t-i0-und&num=3`)
						.then(res => res.json())
						.then(data => {
							if (data[0] === 'SUCCESS') {
								const suggestions = data[1][0][1];
								suggestionP.innerHTML = '';
								suggestions.forEach(s => {
									const span = document.createElement('span');
									span.textContent = s;
									span.style.marginRight = '10px';
									span.style.cursor = 'pointer';
									span.style.color = '#005d67';
									span.style.textDecoration = 'underline';

									span.addEventListener('click', (e) => {
										e.stopPropagation();
										categoryInput.value = s;
										suggestionP.style.display = 'none';
										categoryInput.focus();
										updateList(s);
									});
									suggestionP.appendChild(span);
								});
								suggestionP.style.display = 'block';
							}
						})
						.catch(() => {
							suggestionP.style.display = 'none';
						});
				}

				document.getElementById("seriesFirst").value = 'true';
				document.getElementById("descriptionSection").classList.remove("d-none");

				document.getElementById("next-step").classList.add("d-none");
				document.getElementById("step1Submit").classList.remove("d-none");
				document.getElementById("categoryDropdown").classList.remove("d-none");
				document.getElementById("divisionDropdown").classList.remove("d-none");
				document.getElementById("imageSection").classList.remove("d-none");
					// element.classList.remove("d-none");
			}
		}

	document.addEventListener('DOMContentLoaded', function () {
		const categoryInput = document.getElementById('series_input');
		const selectedInput = document.getElementById('story-series');

		categoryInput.addEventListener('input', function () {
			updateList(this.value);
		});

		selectedInput.addEventListener('click', function () {
			const dropdown = document.getElementById('category_dropdown');
			dropdown.addEventListener('click', function (e) {
				const selectedValue = e.target.textContent.trim();
				const storyCategory = document.getElementById('story-category');

				if (selectedValue !== "தொடர்கதை அல்ல") {
					// var element = document.getElementById("divisionDropdown");
					// element.classList.remove("d-none");

					const seriesFirst = document.getElementById('seriesFirst').value;
					if (!seriesFirst || seriesFirst == 'false') {
						var element = document.getElementById("categoryDropdown");
						element.classList.add("d-none");
						document.getElementById("imageSection").classList.add("d-none");
					} else {
						storyCategory.disabled = true;
					}
				} else {
					storyCategory.disabled = false;
					var element = document.getElementById("divisionDropdown");
					element.classList.add("d-none");

					var element = document.getElementById("categoryDropdown");
					element.classList.remove("d-none");
					document.getElementById("imageSection").classList.remove("d-none");
				}
			});

			setTimeout(() => {
				categoryInput.focus();
				updateList('');
			}, 150);
		});
	});

	document.getElementById('write-story-form').addEventListener('submit', function (e) {
		e.preventDefault();

		jQuery('#saveDraft, .btn[type="submit"], #prev-step').prop('disabled', true);

		const storyCompetition = document.getElementById('story-competition')?.value || '';
		const isCompetitionPage = document.getElementById('story-from-competition')?.value;
		const seriesFirst = document.getElementById('seriesFirst').value;
		const title = document.getElementById('story-title').value;
		const category = document.getElementById('story-category').value;
		const series = document.getElementById('story-series').value;
		const division = (series != "தொடர்கதை அல்ல") ? document.getElementById('story-division').value : '';
		const description = (series != "தொடர்கதை அல்ல" && seriesFirst == 'true') ? document.getElementById('story-description').value : '';
		const content = document.getElementById('story-content').value;
		const imageInput = document.getElementById('story-image');
		const postId = document.getElementById('editPostId').value;

		let errors = [];

		if (isCompetitionPage == 'true' && storyCompetition === '') {
			errors.push({ field: 'competition', message: 'போட்டிகள் is required.' });
		}

		if (title === '') {
			errors.push({ field: 'title', message: 'தலைப்பு is required.' });
		}

		if (!document.getElementById('categoryDropdown').classList.contains('d-none') && category === '') {
			errors.push({ field: 'category', message: 'வகை is required.' });
		}

		if (series === '') {
			errors.push({ field: 'series', message: 'தொடர்கதை is required.' });
		}

		// Show errors
		jQuery.each(errors, function (index, error) {
			jQuery('.story-' + error.field).after(
				'<p class="text-danger error-message mt-2 small">' + error.message + '</p>'
			);
		});

		if (errors.length == 0) {
			const formData = new FormData();
			formData.append('action', 'save_story');
			formData.append('competition', storyCompetition);
			formData.append('title', title);
			formData.append('category', category);
			formData.append('series', series);
			formData.append('division', division);
			formData.append('description', description);
			formData.append('content', content);

			if (postId) {
				formData.append('post_id', postId);
			}

			if (imageInput.files.length > 0) {
				formData.append('story_image', imageInput.files[0]);
			}

			fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
				method: 'POST',
				body: formData
			})
				.then(res => res.json())
				.then(response => {
					if (typeof response.data === 'object' && response.data.content) {
						const elements = document.getElementsByClassName('trumbowyg-box');
						if (elements.length > 0) {
							const target = elements[0];
							const error = document.createElement('p');
							error.className = 'text-danger error-message mt-2 small';
							error.textContent = response.data.content;
							target.parentNode.insertBefore(error, target.nextSibling);
						}
					}

					if (response.success) {
						var element = document.getElementById("draftAlert");
						element.classList.add("d-none");

						document.getElementById('save-result').innerHTML = response.success
							? `<div class="alert alert-success">${response.data}</div>`
							: `<div class="alert alert-danger">${response.data}</div>`;

						if (postId) {
							window.location.href = "<?php echo esc_url( home_url( '/my-creations' ) ); ?>";
						} else {
							if ((series != "தொடர்கதை அல்ல" && seriesFirst == 'true')) {
								window.location.href = "<?php echo esc_url( site_url('/story-success/?status=series') ); ?>";
							} else {
								window.location.href = "<?php echo esc_url( site_url('/story-success/?status=other') ); ?>";
							}
						}

						jQuery('#saveDraft, .btn[type="submit"], #prev-step').prop('disabled', false);
					}
				});
		}
	});

	// draft save #
	let autoSaveTimeout;
	let lastDraftId = null;

	function autoSaveDraft(isAutoSave) {
		const storyCompetition = document.getElementById('story-competition')?.value || '';
		const title    = document.getElementById('story-title').value;
		const content  = document.getElementById('story-content').value;
		const category = document.getElementById('story-category')?.value || '';
		const series   = document.getElementById('story-series')?.value || '';
		const division = document.getElementById('story-division')?.value || '';
		const description   = document.getElementById('story-description')?.value || '';
		const imageInput = document.getElementById('story-image');
		const postId = document.getElementById('editPostId').value;

		if (!title && !content) return;

		const formData = new FormData();
		formData.append('action', 'save_draft');
		formData.append('competition', storyCompetition);
		formData.append('title', title);
		formData.append('content', content);
		formData.append('category', category);
		formData.append('series', series);
		formData.append('division', division);
		formData.append('description', description);
		formData.append('status', 'draft');

		if (postId) {
			formData.append('post_id', postId);
		}

		if (imageInput && imageInput.files.length > 0) {
			formData.append('story_image', imageInput.files[0]);
		}

		fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
			method: 'POST',
			body: formData
		})
		.then(res => res.json())
		.then(response => {
			if (response.success && !isAutoSave) {
				lastDraftId = response.data.post_id;
				var element = document.getElementById("draftAlert");
				element.classList.remove("d-none");

				if (postId) {
					window.location.href = "<?php echo esc_url( home_url( '/my-creations' ) ); ?>";
				} else {
					// location.reload();
					window.location.href = "<?php echo esc_url( site_url('/story-success/?status=draft') ); ?>";
				}
			}
		});
	}

	// fetch('<?php echo esc_url( admin_url('admin-ajax.php') ); ?>?action=get_last_draft_story')
	// 	.then(res => res.json())
	// 	.then(response => {
	// 		if (response.success && response.data) {
	// 			const data = response.data;

	// 			lastDraftId = data.draft_id;
	// 			document.getElementById('story-title').value = data.title || '';
	// 			document.getElementById('story-content').value = data.content || '';
	// 			if (data.category) {
	// 				document.getElementById('story-category').value = data.category;
	// 			}
	// 			if (data.series) {
	// 				document.getElementById('story-series').value = data.series;
	// 			}
	// 			if (data.series && data.series !== 'தொடர்கதை அல்ல') {
	// 				document.getElementById('divisionDropdown').classList.remove('d-none');
	// 				document.getElementById('story-division').value = data.division || '';
	// 			}

	// 			if (data.description) {
	// 				document.getElementById('descriptionSection').classList.remove('d-none');
	// 				document.getElementById('story-description').value = data.description || '';
	// 			}

	// 			if (data.image_url) {
	// 				const imgPreview = document.createElement('img');
	// 				imgPreview.src = data.image_url;
	// 				imgPreview.alt = "Uploaded Image Preview";
	// 				imgPreview.style.maxWidth = "100px";
	// 				document.getElementById('story-image').parentElement.appendChild(imgPreview);
	// 			}

	// 			var element = document.getElementById("draftAlert");
	// 			element.classList.remove("d-none");
	// 		}
	// 	});

	document.addEventListener('DOMContentLoaded', () => {
		// Get postId from URL
		const urlParams = new URLSearchParams(window.location.search);
		const postId = urlParams.get('id');
		const isCompetitionPage = document.getElementById('story-from-competition')?.value;

		if (postId) {
			fetch('<?php echo esc_url( admin_url('admin-ajax.php') ); ?>', {
				method:'POST',
				headers:{'Content-Type': 'application/x-www-form-urlencoded'},
				body:'action=get_story_by_id&post_id=' + postId
			})
			.then(response => response.json()) 
			.then(data => {
				if (data.success && data.data) {
					// populate form fields here
					if (isCompetitionPage == 'true' && data.data?.competition) {
						// document.getElementById('story-competition').value = data.data.competition;

						const competitionSelect = document.getElementById('story-competition');
						if (competitionSelect) {
							competitionSelect.value = data.data.competition;
							competitionSelect.disabled = true;

							competitionSelect.dispatchEvent(new Event('change'));
						}
					}
					document.getElementById('story-title').value = data.data.title;
					document.getElementById('story-content').value = data.data.content;

					if (data.data.category) {
						document.getElementById('story-category').value = data.data.category;
					} else {
						document.getElementById("categoryDropdown").classList.add("d-none");
						document.getElementById("imageSection").classList.add("d-none");
					}
					
					if (data.data.series) {
						document.getElementById('story-series').value = data.data.series;
					}
					
					if (data.data.division) {
						document.getElementById('divisionDropdown').classList.remove('d-none'); 
						document.getElementById('story-division').value = data.data.division;
					}
					
					if (data.data.description) {
						document.getElementById('descriptionSection').classList.remove('d-none'); 
						document.getElementById('story-description').value = data.data.description;
						document.getElementById('seriesFirst').value = 'true';

						document.getElementById("next-step").classList.add("d-none");
						document.getElementById("step1Submit").classList.remove("d-none");
					}
					
					if (data.data.image_url) {
						const imgPreview = document.createElement('img');
						imgPreview.src = data.data.image_url;
						imgPreview.alt = "Uploaded Image Preview";
						imgPreview.style.maxWidth = "100px";
						document.getElementById('story-image').parentElement.appendChild(imgPreview);
					}

					updateList();
				}
			})
			.catch(err => console.error(err)); 
		} else {
			document.getElementById('story-series').value = 'தொடர்கதை அல்ல';
			updateList();
		}
	});

	document.addEventListener('DOMContentLoaded', function () {
		const categoryList = document.getElementById('category_list');
		const storySeries = document.getElementById('story-series');

		// Function to remove the specific list item
		function removeNonSeriesOption() {
			const items = categoryList.querySelectorAll('.dropdown-item');
			items.forEach(item => {
				if (item.textContent.trim() === 'தொடர்கதை அல்ல') {
					item.parentElement.remove(); // remove <li>
				}
			});
		}

		// Function to add the specific list item if not exists
		function addNonSeriesOption() {
			const exists = Array.from(categoryList.querySelectorAll('.dropdown-item')).some(item => 
				item.textContent.trim() === 'தொடர்கதை அல்ல'
			);

			if (!exists) {
				const li = document.createElement('li');
				const a = document.createElement('a');
				a.href = '#';
				a.className = 'dropdown-item';
				a.textContent = 'தொடர்கதை அல்ல';
				li.appendChild(a);
				categoryList.prepend(li);
			}
		}


		const competitionSelect = document.getElementById('story-competition');
		
		if (competitionSelect) {
			competitionSelect.addEventListener('change', function () {
				const competitionId = this.value;

				if (!competitionId) return;

				fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
					method: 'POST',
					headers: {'Content-Type': 'application/x-www-form-urlencoded'},
					body: new URLSearchParams({
						action: 'get_competition_details',
						competition_id: competitionId
					})
				})
				.then(res => res.json())
				.then(data => {
					if (data.success) {
						const details = data.data;

						if (details) {
							const urlParams = new URLSearchParams(window.location.search);
							const postId = urlParams.get('id');
							if (postId) {
								storySeries.disabled = true;
							} else {
								storySeries.disabled = false;
								if (details.series == 'தொடர்கதை') {
									storySeries.value = '';
									removeNonSeriesOption();
								} else {
									storySeries.value = details.series;
									storySeries.disabled = true;
									addNonSeriesOption();
								}
							}

							document.getElementById('story-category').value = details.category_id;
							document.getElementById('story-category').disabled = true;
						}
					} else {
						alert('Failed to fetch competition data');
					}
				});
			});
		}
	});

</script>