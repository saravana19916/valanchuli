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

							$current_user_id = get_current_user_id();

							$series_terms = get_terms([
								'taxonomy'   => 'series',
								'hide_empty' => false,
								'meta_query' => [
									[
										'key' => 'is_deleted',
										'compare' => 'NOT EXISTS',
									],
								],
							]);

							$filtered_series = array_filter($series_terms, function ($term) use ($current_user_id) {
								if ($term->name === 'தொடர்கதை அல்ல') {
									return false;
								}

								$args = [
									'post_type'      => 'post',
									'posts_per_page' => -1,
									'post_status'    => 'publish',
									'author'         => $current_user_id,
									'tax_query'      => [
										[
											'taxonomy' => 'series',
											'field'    => 'term_id',
											'terms'    => $term->term_id,
										],
									],
								];

								if ( isset($_GET['from']) && $_GET['from'] === 'competition' ) {
									$args['meta_query'][] = [
										'key'     => 'competition',
										'compare' => 'EXISTS',
									];

								} else {
									$args['meta_query'][] = [
										'key'     => 'competition',
										'compare' => 'NOT EXISTS',
									];
								}

								$query = new WP_Query($args);

								return $query->have_posts();
							});

						?>
                        <div class="mb-4">
                            <label class="form-label">கதையின் வகை <span style="color: red;">*</span></label>
							<select class="form-select login-form-group story-type" id="story-type">
								<option value="தொடர்கதை அல்ல">தொடர்கதை அல்ல</option>
                                <option value="தொடர்கதை">தொடர்கதை</option>
							</select>
                            <p class="my-2 fs-12px" style="color: gray;"><i>உங்கள் படைப்பு ஏதேனும் தொடர்கதையாக  இருந்தால் மட்டும் தேர்ந்தெடுக்கவும்.</i></p>
                        </div>

                        <div class="mb-4 d-none story-sub-type" id="story-sub-type">
                            <label class="form-label">கதையின் வகையை தேர்வுசெய்க <span style="color: red;">*</span></label>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="storySubType" id="seriesStory" value="series">
                                <label class="form-check-label" for="seriesStory">புதிய தொடர்கதை</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="storySubType" id="episodeStory" value="episode">
                                <label class="form-check-label" for="episodeStory">அத்தியாயம்</label>
                            </div>
                        </div>

                        <div class="mb-4 d-none" id="existingSeries">
							<label class="form-label">தொடர்கதை <span style="color: red;">*</span></label>
							<select class="form-select login-form-group story-series" id="my-series">
								<option value="">-- select --</option>
								<?php
								foreach ($filtered_series as $series) {
        							echo '<option value="' . esc_attr($series->name) . '">' . esc_html($series->name) . '</option>';
								}
								?>
							</select>
						</div>

						<div class="mb-4">
							<label class="form-label">தலைப்பு <span style="color: red;">*</span></label>
							<input type="text" class="form-control tamilwriter login-form-group story-title tamil-suggestion-input" id="story-title">
							<p class="tamil-suggestion-box mt-2" data-suggestion-for="story-title" style="display: none;"></p>
							<p class="my-2 fs-12px d-none" style="color: gray;" id="titleInfo"><i>உங்கள் தொடர்கதையின் தலைப்பை மீண்டும் டைப் செய்யுங்கள்</i></p>
							<p class="my-2 fs-12px d-none" style="color: gray;" id="titleEpisodeInfo"><i>இங்கு உங்கள் கதையின் எந்த எபிசோடை பதிவிட விரும்புகிறீர்களோ அந்த எபிசோட் எண்ணை டைப் செய்யுங்கள்(உதாரணம் -  எபிசோட் 1, எபிசோட் 2)</i></p>
						</div>


						<div class="mb-4" id="categoryDropdown">
							<label class="form-label">வகை <span style="color: red;">*</span></label>
							<select class="form-select login-form-group story-category" id="story-category">
								<option value="">-- select --</option>
								<?php
								$categories = get_categories([
									'taxonomy' => 'category',
									'hide_empty' => false,
									'exclude'    => [
										get_cat_ID('Uncategorized'),
										get_cat_ID('அத்தியாயம்')
									],
								]);
								foreach ($categories as $cat) {
									$disabled = ($cat->name === 'தொடர்கதை') ? 'disabled' : '';
        							echo '<option value="' . esc_attr($cat->term_id) . '" ' . $disabled . '>' . esc_html($cat->name) . '</option>';
								}
								?>
							</select>
						</div>

						<div class="mb-4 d-none" id="divisionDropdown">
							<label class="form-label">பிரிவுகள் <span style="color: red;">*</span></label>
							<?php
								$divisions = get_terms(array(
									'taxonomy'   => 'division',
									'hide_empty' => false,
									'meta_query' => [
										[
											'key' => 'is_deleted',
											'compare' => 'NOT EXISTS',
										],
									],
								));

								if (!empty($divisions) && !is_wp_error($divisions)) :
							?>
									<select class="form-select login-form-group story-division" id="story-division" name="story-division">
										<option value="">Select Division</option>
										<?php foreach ($divisions as $division) : ?>
											<option value="<?php echo esc_attr($division->term_id); ?>">
												<?php echo esc_html($division->name); ?>
											</option>
										<?php endforeach; ?>
									</select>
								<?php endif; ?>
							<p class="my-2 fs-12px" style="color: gray;"><i>இந்த படைப்பு எந்த வகையை சேர்ந்தது என்பதை தேர்ந்தெடுக்கவும் (உதாரணம்: காதல், குடும்பம், நகைச்சுவை, தொடர்கதை)</i></p>
						</div>

						<div class="mb-4 d-none" id="descriptionSection">
							<label class="form-label">Description</label>
							<textarea class="form-control text-primary-color login-form-group tamil-suggestion-input story-description" id="story-description" name="story-description" rows="4" placeholder="Short description"></textarea>
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
						<button type="button" class="btn btn-primary d-none" id="step1SaveDraft"><i class="fa-solid fa-floppy-disk"></i>&nbsp;
						Save Draft</button>
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
				<button type="submit" class="btn btn-primary me-2" id="step2Submit"><i class="fa-solid fa-floppy-disk"></i>&nbsp;
					சமர்ப்பிக்க</button>
				<button type="button" class="btn btn-primary" id="saveDraft"><i class="fa-solid fa-floppy-disk"></i>&nbsp;
					Save Draft</button>
			</div>
		</form>
	<?php } ?>
</div>

<?php get_footer(); ?>

<script>
    jQuery(document).ready(function () {
        function toggleStorySections() {
			jQuery('.error-message').remove();

            let storyType = jQuery("#story-type").val();
			jQuery("#story-category").val('');
			jQuery("#story-division").val('');
			jQuery("#story-description").val('');
			jQuery("#story-image").val('');

            if (storyType === "தொடர்கதை") {
                jQuery("#story-sub-type").removeClass("d-none");

                if (jQuery("#episodeStory").is(":checked")) {
                    jQuery("#existingSeries").removeClass("d-none");
                    jQuery("#categoryDropdown").addClass("d-none");
                    jQuery("#divisionDropdown").addClass("d-none");
                    jQuery("#descriptionSection").addClass("d-none");
                    jQuery("#imageSection").addClass("d-none");
                    jQuery("#next-step").removeClass("d-none");
                    jQuery("#step1Submit").addClass("d-none");
                    jQuery("#step1SaveDraft").addClass("d-none");
					jQuery("#existingSeries").val('');
                } else {
                    jQuery("#existingSeries").addClass("d-none");
                    jQuery("#categoryDropdown").removeClass("d-none");
                    jQuery("#divisionDropdown").removeClass("d-none");
                    jQuery("#descriptionSection").removeClass("d-none");
                    jQuery("#imageSection").removeClass("d-none");
                    jQuery("#next-step").addClass("d-none");
                    jQuery("#step1Submit").removeClass("d-none");
                    jQuery("#step1SaveDraft").removeClass("d-none");

					const storyCategory = document.getElementById('story-category');
					storyCategory.disabled = true;
					for (let i = 0; i < storyCategory.options.length; i++) {
						if (storyCategory.options[i].text.trim() === "தொடர்கதை") {
							storyCategory.selectedIndex = i;
							break;
						}
					}
                }

            } else {
				document.getElementById('story-category').disabled = false;
				document.getElementById('episodeStory').checked = false;
				document.getElementById('seriesStory').checked = false;
				jQuery("#existingSeries").addClass("d-none");
                jQuery("#story-sub-type").addClass("d-none");
                jQuery("#divisionDropdown").addClass("d-none");
                jQuery("#descriptionSection").addClass("d-none");
				jQuery("#categoryDropdown").removeClass("d-none");
				jQuery("#imageSection").removeClass("d-none");
				jQuery("#next-step").removeClass("d-none");
				jQuery("#step1Submit").addClass("d-none");
				jQuery("#step1SaveDraft").addClass("d-none");
            }
        }

        jQuery("#story-type").on("change", toggleStorySections);
        jQuery("#episodeStory, #seriesStory").on("change", toggleStorySections);

        toggleStorySections();


		// competition change event start
		function competitionChange() {
			const competitionSelect = document.getElementById('story-competition');
			
			if (competitionSelect) {
				competitionSelect.addEventListener('change', function () {
					const competitionId = this.value;

					document.getElementById('story-type').value = 'தொடர்கதை அல்ல';
					document.getElementById('story-type').disabled = false;

					document.getElementById('story-category').value = '';
					document.getElementById('story-category').disabled = false;

					if (!competitionId) {
						jQuery("#story-type").trigger("change");
						return;
					}

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
							console.log("details", details);

							if (details) {
								// const urlParams = new URLSearchParams(window.location.search);
								// const postId = urlParams.get('id');
								// if (postId) {
								// 	storySeries.disabled = true;
								// } else {
								// 	if (details.series == 'தொடர்கதை') {
								// 		storySeries.value = '';
								// 		removeNonSeriesOption();
								// 	} else {
								// 		storySeries.value = details.series;
								// 		storySeries.disabled = true;
								// 		addNonSeriesOption();
								// 	}
								// }

								document.getElementById('story-type').value = details.series;
								document.getElementById('story-type').disabled = true;
								jQuery("#story-type").trigger("change");
								document.getElementById('story-category').value = details.category_id;
								document.getElementById('story-category').disabled = true;
							}
						} else {
							alert('Failed to fetch competition data');
						}
					});
				});
			}
		}

		competitionChange();
		// competition change event end

		// write page edit start
		function fetchStory() {
			const urlParams = new URLSearchParams(window.location.search);
			let postId = urlParams.get('id');
			const isCompetitionPage = document.getElementById('story-from-competition')?.value;
			const createAction = urlParams.get('create');

			if (createAction == 'episode') {
				postId = urlParams.get('postId');
			}

			if (postId) {
				fetch('<?php echo esc_url( admin_url('admin-ajax.php') ); ?>', {
					method:'POST',
					headers:{'Content-Type': 'application/x-www-form-urlencoded'},
					body:'action=get_story_by_id&post_id=' + postId
				})
				.then(response => response.json()) 
				.then(data => {
					if (data.success && data.data) {
						if (isCompetitionPage == 'true' && data.data?.competition) {
							const competitionSelect = document.getElementById('story-competition');
							if (competitionSelect) {
								competitionSelect.value = data.data.competition;
								competitionSelect.disabled = true;

								competitionSelect.dispatchEvent(new Event('change'));
							}
						}

						console.log("createAction", createAction);

						if (createAction == 'episode') {
							document.getElementById('story-type').value = 'தொடர்கதை';
							jQuery("#story-type").trigger("change");
							document.getElementById('story-type').disabled = true;

							document.getElementById('episodeStory').checked = true;
							jQuery("#episodeStory").trigger("change");
							document.getElementById('seriesStory').disabled = true;
							document.getElementById('episodeStory').disabled = true;

							setTimeout(() => {
								document.getElementById('my-series').value = data.data.series;
								document.getElementById('my-series').disabled = true;
							}, 600);
						} else {
							if (data.data?.series) {
								if (data.data?.series == 'தொடர்கதை அல்ல') {
									document.getElementById('story-type').value = 'தொடர்கதை அல்ல';
								} else {
									document.getElementById('story-type').value = 'தொடர்கதை';
								}

								jQuery("#story-type").trigger("change");
								document.getElementById('story-type').disabled = true;

								if (data.data?.division || data.data?.series == 'தொடர்கதை') {
									document.getElementById('seriesStory').checked = true;
									jQuery("#seriesStory").trigger("change");
								} else if (data.data?.series != 'தொடர்கதை அல்ல') {
									document.getElementById('episodeStory').checked = true;
									jQuery("#episodeStory").trigger("change");

									setTimeout(() => {
										document.getElementById('my-series').value = data.data.series;
										document.getElementById('my-series').disabled = true;
									}, 600);
								}

								document.getElementById('seriesStory').disabled = true;
								document.getElementById('episodeStory').disabled = true;
							}


							document.getElementById('story-title').value = data.data.title;
							document.getElementById('story-content').value = data.data.content;

							if (data.data.category) {
								document.getElementById('story-category').value = data.data.category;
								document.getElementById('story-category').disabled = true;
							}

							if (data.data.division) {
								setTimeout(() => {
									document.getElementById('story-division').value = data.data.division;
									document.getElementById('story-description').value = data.data.description;
								}, 600);
							}
						}
					}
				})
				.catch(err => console.error(err)); 
			}
		}

		fetchStory()
		// write page edit end

		jQuery('#next-step').on('click', function () {
			const storyCompetition = document.getElementById('story-competition')?.value;
			const isCompetitionPage = document.getElementById('story-from-competition')?.value;

			const storyType = document.getElementById('story-type')?.value;
			const storySubType = document.querySelector('input[name="storySubType"]:checked')?.value;

			const title = document.getElementById('story-title').value;
			const category = document.getElementById('story-category').value;
			const series = document.getElementById('my-series').value;

			jQuery('.error-message').remove();

			let errors = [];

			// Validation checks
			if (isCompetitionPage == 'true' && storyCompetition === '') {
				errors.push({ field: 'competition', message: 'போட்டிகள் is required.' });
			}

			if (storyType === '') {
				errors.push({ field: 'type', message: 'கதையின் வகை is required.' });
			}

			if (storyType && storyType == 'தொடர்கதை' && (!storySubType || storySubType == '')) {
				errors.push({ field: 'sub-type', message: 'கதையின் வகையை தேர்வுசெய்க is required.' });
			}

			if (title === '') {
				errors.push({ field: 'title', message: 'தலைப்பு is required.' });
			}

			if (!document.getElementById('categoryDropdown').classList.contains('d-none') && category === '') {
				errors.push({ field: 'category', message: 'வகை is required.' });
			}

			if (storyType == 'தொடர்கதை' && storySubType == 'episode' && series === '') {
				errors.push({ field: 'series', message: 'தொடர்கதை is required.' });
			}

			// Show errors
			jQuery.each(errors, function (index, error) {
				jQuery('.story-' + error.field).after(
					'<p class="text-danger error-message mt-2 small">' + error.message + '</p>'
				);
			});

			if (errors.length == 0) {
				jQuery('#step-1').hide();
				jQuery('#step-2').show();

				if (!jQuery('#story-content').data('trumbowyg')) {
					jQuery("#story-content").trumbowyg({
						btns: [
							['formatting'],
							['fontsize'],
							["bold", "italic", "underline"],
							['justifyLeft', 'justifyCenter', 'justifyRight'],
							['unorderedList', 'orderedList'],
							["emoji"],
							['uploadImage']
						],
						btnsDef: {
							uploadImage: {
								fn: function() {
									const fileInput = jQuery('<input type="file" accept="image/*">');
									fileInput.on('change', function() {
										const file = this.files[0];
										const formData = new FormData();
										formData.append('file', file);
										formData.append('action', 'trumbowyg_upload');

										jQuery.ajax({
											url: my_ajax_object.ajax_url,
											type: 'POST',
											data: formData,
											processData: false,
											contentType: false,
											success: function(response) {
												if (response.success && response.data && response.data.url) {
													jQuery('#story-content').trumbowyg('execCmd', {
														cmd: 'insertHTML',
														param: `<img src="${response.data.url}" alt="">`
													});
												}
											}
										});
									});
									fileInput.trigger('click');
								},
								title: 'Upload Image',
								ico: 'insertImage'
							}
						}
					}).on('tbwchange tbwinit', updateWordCount);

					const suggestionBox = jQuery('#tanglishSuggestions');
					let editor = jQuery('#story-content').next('.trumbowyg-box').find('.trumbowyg-editor')[0];

					if (!editor) {
						editor = document.querySelector('.trumbowyg-editor');
					}

					if (!editor) {
						console.error('Editor not found');
						return;
					}

					let activeRequests = 0;
					let latestSuggestions = [];
					let lastRange = null;
					let skipNextInput = false;

					editor.addEventListener('input', () => {
						if (skipNextInput) {
							skipNextInput = false;
							return;
						}

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

						jQuery('#content-loader').show();
						jQuery('#content-loader-2').show();

						jQuery('#saveDraft, #step2Submit, #prev-step').prop('disabled', true);

						activeRequests++;

						fetch(`https://inputtools.google.com/request?text=${encodeURIComponent(lastWord)}&itc=ta-t-i0-und&num=5`)
						.then(res => res.json())
						.then(data => {
							if (data[0] === "SUCCESS") {
								const suggestions = data[1][0][1];
								latestSuggestions = suggestions;
								lastRange = range;
								showSuggestions(suggestions, range);
							} else {
								latestSuggestions = [];
								suggestionBox.hide();
							}
						})
						.catch(() => suggestionBox.hide())
						.finally(() => {
							activeRequests--;

							if (activeRequests === 0) {
								jQuery('#saveDraft, #step2Submit, #prev-step').prop('disabled', false);
								jQuery('#content-loader').hide();
								jQuery('#content-loader-2').hide();
							}
						});
					});

					editor.addEventListener('beforeinput', (e) => {
						const isSpace =
							e.inputType === 'insertText' && e.data === ' ';

						const isEnter =
							e.inputType === 'insertLineBreak' ||
							e.inputType === 'insertParagraph';

						if ((isSpace || isEnter) && latestSuggestions.length > 0) {
							e.preventDefault();

							// Replace last word
							replaceLastWord(latestSuggestions[0]);

							// Insert space or newline safely
							if (isSpace) {
								document.execCommand('insertText', false, ' ');
							} else if (isEnter) {
								document.execCommand('insertHTML', false, '<br>');
							}

							latestSuggestions = [];
							suggestionBox.hide();
						}
					});

					function showSuggestions(suggestions, range) {
						suggestionBox.empty();

						suggestions.forEach(s => {
						jQuery('<li>')
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

					function replaceLastWord(word) {
						const sel = window.getSelection();
						if (!sel.rangeCount) return;

						const range = sel.getRangeAt(0);
						const node = range.startContainer;

						if (node.nodeType !== Node.TEXT_NODE) return;

						const text = node.textContent;
						const end = range.startOffset;

						let start = end;
						while (start > 0 && !/\s/.test(text[start - 1])) {
							start--;
						}

						const wordRange = document.createRange();
						wordRange.setStart(node, start);
						wordRange.setEnd(node, end);
						wordRange.deleteContents();

						const textNode = document.createTextNode(word);
						wordRange.insertNode(textNode);

						range.setStartAfter(textNode);
						range.collapse(true);

						sel.removeAllRanges();
						sel.addRange(range);

						editor.focus();
					}
				}

				// Optional: Adjust editor height
				jQuery('.trumbowyg-editor').css({
					height: '35rem',
					overflow: 'auto'
				});
			}
		});

		function updateWordCount() {
			const content = jQuery('#story-content').trumbowyg('html');
			const textOnly = jQuery('<div>').html(content).text();
			const wordCount = textOnly.trim().split(/\s+/).filter(word => word.length > 0).length;
			jQuery('#word-count').text(wordCount);
		}

		jQuery('#prev-step').on('click', function () {
			jQuery('#step-2').hide();
			jQuery('#step-1').show();
		});

		// form save start
		document.getElementById('write-story-form').addEventListener('submit', function (e) {
			e.preventDefault();

			const storyCompetition = document.getElementById('story-competition')?.value || '';
			const isCompetitionPage = document.getElementById('story-from-competition')?.value;

			const storyType = document.getElementById('story-type')?.value;
			const storySubType = document.querySelector('input[name="storySubType"]:checked')?.value;
			
			const title = document.getElementById('story-title').value;
			const category = document.getElementById('story-category').value;
			const series = document.getElementById('my-series').value;
			const division = (storySubType == "series") ? document.getElementById('story-division').value : '';
			const description = (storySubType == "series") ? document.getElementById('story-description').value : '';
			const content = document.getElementById('story-content').value;
			const imageInput = document.getElementById('story-image');
			const postId = document.getElementById('editPostId').value;

			jQuery('.error-message').remove();

			jQuery('#saveDraft, #step2Submit, #prev-step').prop('disabled', true);

			let errors = [];

			if (isCompetitionPage == 'true' && storyCompetition === '') {
				errors.push({ field: 'competition', message: 'போட்டிகள் is required.' });
			}

			if (storyType === '') {
				errors.push({ field: 'type', message: 'கதையின் வகை is required.' });
			}

			if (storyType && storyType == 'தொடர்கதை' && (storySubType == '' || !storySubType)) {
				errors.push({ field: 'sub-type', message: 'கதையின் வகையை தேர்வுசெய்க is required.' });
			}

			if (title === '') {
				errors.push({ field: 'title', message: 'தலைப்பு is required.' });
			}

			if (!document.getElementById('categoryDropdown').classList.contains('d-none') && category === '') {
				errors.push({ field: 'category', message: 'வகை is required.' });
			}

			if (storyType == 'தொடர்கதை' && storySubType == 'episode' && series === '') {
				errors.push({ field: 'series', message: 'தொடர்கதை is required.' });
			}

			if (storyType == 'தொடர்கதை' && storySubType == 'series' && division == '') {
				errors.push({ field: 'division', message: 'பிரிவுகள் is required.' });
			}

			// Show errors
			jQuery.each(errors, function (index, error) {
				jQuery('.story-' + error.field).after(
					'<p class="text-danger error-message mt-2 small">' + error.message + '</p>'
				);
			});

			if (errors.length == 0) {

				if (storySubType == "episode") {
					const wordCount = jQuery('#word-count').text();
					if (isCompetitionPage || isCompetitionPage == 'true') {
						const minWords = <?php echo (int) get_option('competition_min_words'); ?>;
						const maxWords = <?php echo (int) get_option('competition_max_words'); ?>;

						if (wordCount < minWords || wordCount > maxWords) {
							e.preventDefault();
							alert(`Your story must be between ${minWords} and ${maxWords} words. You wrote ${wordCount}.`);
							return;
						}
					} else {
						const minWords = <?php echo (int) get_option('series_min_words'); ?>;
						const maxWords = <?php echo (int) get_option('series_max_words'); ?>;

						if (wordCount < minWords || wordCount > maxWords) {
							e.preventDefault();
							alert(`Your story must be between ${minWords} and ${maxWords} words. You wrote ${wordCount}.`);
							return;
						}
					}
				}

				const formData = new FormData();
				formData.append('action', 'save_story');
				formData.append('competition', storyCompetition);
				formData.append('storyType', storyType);
				formData.append('storySubType', storySubType);
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
						if (typeof response.data === 'object' && response.data.title) {
							jQuery('.story-title').after(
								'<p class="text-danger error-message mt-2 small">' + response.data.title + '</p>'
							);
						}

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
								if (storySubType == "series") {
									window.location.href = "<?php echo esc_url( site_url('/story-success/?status=series') ); ?>";
								} else {
									window.location.href = "<?php echo esc_url( site_url('/story-success/?status=other') ); ?>";
								}
							}

							jQuery('#saveDraft, #step2Submit, #prev-step').prop('disabled', false);
						} else {
							jQuery('#step-2').hide();
							jQuery('#step-1').show();
							jQuery('#saveDraft, #step2Submit, #prev-step').prop('disabled', false);
						}
					});
			}
		});
		// form save end

		// draft save start
		jQuery('#story-content').on('tbwchange', function () {
			startAutoSave();
		});

		let autoSaveInterval = null;
		function startAutoSave() {
			autoSaveInterval = setInterval(function () {
				autoSaveDraft(true);
			}, 2 * 60 * 1000);
		}

		startAutoSave();

		jQuery('#saveDraft').click(function() {
			autoSaveDraft(false);
		});

		jQuery('#step1SaveDraft').click(function() {
			autoSaveDraft(false);
		});

		let autoSaveTimeout;
		let lastDraftId = null;
		let currentPostId = null;

		function autoSaveDraft(isAutoSave) {
			const storyCompetition = document.getElementById('story-competition')?.value || '';
			const isCompetitionPage = document.getElementById('story-from-competition')?.value;

			const storyType = document.getElementById('story-type')?.value;
			const storySubType = document.querySelector('input[name="storySubType"]:checked')?.value;

			const title    = document.getElementById('story-title').value;
			const content  = document.getElementById('story-content').value;
			const category = document.getElementById('story-category')?.value || '';
			const series = document.getElementById('my-series').value;
			const division = (storySubType == "series") ? document.getElementById('story-division').value : '';
			const description = (storySubType == "series") ? document.getElementById('story-description').value : '';
			const imageInput = document.getElementById('story-image');
			let postId = document.getElementById('editPostId').value;

			if (currentPostId) {
				postId = currentPostId;
			};

			if (!title && !content) return;

			const wordCount = jQuery('#word-count').text();

			if (storySubType == "episode") {
				if (isCompetitionPage || isCompetitionPage == 'true') {
					const minWords = <?php echo (int) get_option('competition_min_words'); ?>;
					const maxWords = <?php echo (int) get_option('competition_max_words'); ?>;

					if (wordCount < minWords || wordCount > maxWords) {
						console.log(`Your story must be between ${minWords} and ${maxWords} words. You wrote ${wordCount}.`);
						return;
					}
				} else {
					const minWords = <?php echo (int) get_option('series_min_words'); ?>;
					const maxWords = <?php echo (int) get_option('series_max_words'); ?>;

					if (wordCount < minWords || wordCount > maxWords) {
						console.log(`Your story must be between ${minWords} and ${maxWords} words. You wrote ${wordCount}.`);
						return;
					}
				}
			}

			const formData = new FormData();
			formData.append('action', 'save_draft');
			formData.append('competition', storyCompetition);
			formData.append('storyType', storyType);
			formData.append('storySubType', storySubType);
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
				if (response.success && response.data.post_id) {
					currentPostId = response.data.post_id;
					document.getElementById('editPostId').value = response.data.post_id;
				}

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

				if (!response.success) {
					jQuery('#step-2').hide();
					jQuery('#step-1').show();
					jQuery('#saveDraft, #step2Submit, #prev-step').prop('disabled', false);
				}
			});
		}
		// draft save end
    });
</script>