<?php
/**
 * Template Name: Write Story Page
 */
get_header(); ?>

<div class="container mt-5">
	<div class="row">
		<h5 class="text-center text-primary-color fw-bold"> எழுத </h5>
	</div>

	<?php if ( ! is_user_logged_in() ) { ?>
		<div class="alert alert-warning text-center w-50 mx-auto mt-3" role="alert" id="draftAlert">
			தயவு செய்து உள்நுழையவும். This page is restricted. Please 
			<a href="login" class="alert-link">Login / Register</a> to view this page.
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
						<div class="mb-4">
							<label class="form-label">தலைப்பு <span style="color: red;">*</span></label>
							<input type="text" class="form-control tamilwriter login-form-group story-title tamil-suggestion-input" id="story-title">
							<p class="tamil-suggestion-box mt-2" data-suggestion-for="story-title" style="display: none;"></p>
						</div>

						<?php
							$static_series = ['தொடர்கதை அல்ல'];

							$series_terms = get_terms(['taxonomy' => 'series', 'hide_empty' => false]);
							$filtered_series = array_filter($series_terms, function ($term) {
								return $term->name !== 'தொடர்கதை அல்ல';
							});
							$dynamic_series = [];
							foreach ($filtered_series as $term) {
								$dynamic_series[] = $term->name;
							}
						?>

						<div class="mb-4 dropdown">
							<label for="category_dropdown_input" class="form-label">தொடர்கதை <span style="color: red;">*</span></label>

							<input type="text" readonly class="form-control dropdown-toggle form-select login-form-group story-series_input" id="story-series"
								name="story-series" data-bs-toggle="dropdown" value="தொடர்கதை அல்ல">

							<ul class="dropdown-menu w-100 p-2" id="category_dropdown">
								<li>
									<input type="text" class="form-control mb-2 tamil-suggestion-input" id="series_input"
										placeholder="Type to create or search...">
								</li>
								<div id="category_list"></div>
							</ul>

							<p class="my-2 fs-12px" style="color: gray;"><i>உங்கள் படைப்பு ஏதேனும் தொடர்கதையாக  இருந்தால் மட்டும் தேர்ந்தெடுக்கவும்.</i></p>
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
							<select class="form-select login-form-group" id="story-division" name="sotry-division">
								<option value="">Select Division</option>
								<option value="division1">Division 1</option>
								<option value="division2">Division 2</option>
								<option value="division3">Division 3</option>
							</select>
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
					<label class="form-label">படைப்பை சேர்க்கவும் <span style="color: red;">*</span></label>
					<textarea id="story-content" class="form-control tamilwriter story-content" rows="6"></textarea>
					<ul id="tanglishSuggestions" 
						style="position:absolute; z-index:9999; background:#fff; border:1px solid #ccc; list-style:none; padding:0; margin:0; display:none; min-width:120px;">
					</ul>
					<p class="mt-2 d-block text-primary-color fw-bold">Word Count: <span class="badge bg-primary-color text-highlight-color fw-bold fs-14px p-2" id="word-count">0</span></p>
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

	jQuery(document).ready(function ($) {
		$('#next-step').on('click', function () {

			const title = document.getElementById('story-title').value;
			const category = document.getElementById('story-category').value;
			const series = document.getElementById('story-series').value;

			$('.error-message').remove();

			// Initialize empty array
			let errors = [];

			// Validation checks
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
							["link"],
							["insertImage"],
							["emoji"]
						],
						autogrow: true
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
						.catch(() => suggestionBox.hide());
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
		}

		$('#prev-step').on('click', function () {
			$('#step-2').hide();
			$('#step-1').show();
		});

		// $('#story-content').on('tbwchange', function () {
		// 	clearTimeout(autoSaveTimeout);
		// 	autoSaveTimeout = setTimeout(autoSaveDraft, 2000);
		// });

		$('#saveDraft').click(function() {
			autoSaveDraft();
		});
	});

	document.addEventListener('DOMContentLoaded', function () {
		const categoryInput = document.getElementById('series_input');
		const selectedInput = document.getElementById('story-series');
		const categoryList = document.getElementById('category_list');

		const staticSeries = <?php echo json_encode($static_series, JSON_UNESCAPED_UNICODE); ?>;
		const dynamicSeries = <?php echo json_encode($dynamic_series, JSON_UNESCAPED_UNICODE); ?>;

		const labelMap = {
			...staticSeries,
			...dynamicSeries
		};

		const categories = Object.values(staticSeries).concat(Object.values(dynamicSeries));

		function updateList(filter = '') {
			categoryList.innerHTML = '';
			const filtered = categories.filter(cat => cat.toLowerCase().includes(filter.toLowerCase()));
			
			if (filtered.length > 0) {
				document.getElementById("seriesFirst").value = 'false';
				document.getElementById("descriptionSection").classList.add("d-none");
				document.getElementById("next-step").classList.remove("d-none");
				document.getElementById("step1Submit").classList.add("d-none");
				document.getElementById("categoryDropdown").classList.add("d-none");
				document.getElementById("divisionDropdown").classList.add("d-none");
				document.getElementById("imageSection").classList.add("d-none");

				// if ()

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

		categoryInput.addEventListener('input', function () {
			updateList(this.value);
		});

		selectedInput.addEventListener('click', function () {
			const dropdown = document.getElementById('category_dropdown');
			dropdown.addEventListener('click', function (e) {
				const selectedValue = e.target.textContent.trim();

				if (selectedValue !== "தொடர்கதை அல்ல") {
					// var element = document.getElementById("divisionDropdown");
					// element.classList.remove("d-none");

					// var element = document.getElementById("categoryDropdown");
					// element.classList.add("d-none");
					// document.getElementById("imageSection").classList.add("d-none");
				} else {
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

		const seriesFirst = document.getElementById('seriesFirst').value;
		const title = document.getElementById('story-title').value;
		const category = document.getElementById('story-category').value;
		const series = document.getElementById('story-series').value;
		const division = (series != "தொடர்கதை அல்ல") ? document.getElementById('story-division').value : '';
		const description = (series != "தொடர்கதை அல்ல" && seriesFirst == 'true') ? document.getElementById('story-description').value : '';
		const content = document.getElementById('story-content').value;
		const imageInput = document.getElementById('story-image');

		let errors = [];
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
			formData.append('title', title);
			formData.append('category', category);
			formData.append('series', series);
			formData.append('division', division);
			formData.append('description', description);
			formData.append('content', content);

			if (lastDraftId) {
				formData.append('draft_id', lastDraftId);
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

						location.reload();
					}
				});
		}
	});

	// draft save #
	let autoSaveTimeout;
	let lastDraftId = null;

	function autoSaveDraft() {
		const title    = document.getElementById('story-title').value;
		const content  = document.getElementById('story-content').value;
		const category = document.getElementById('story-category')?.value || '';
		const series   = document.getElementById('story-series')?.value || '';
		const division = document.getElementById('story-division')?.value || '';
		const description   = document.getElementById('story-description')?.value || '';
		const imageInput = document.getElementById('story-image');

		if (!title && !content) return;

		const formData = new FormData();
		formData.append('action', 'save_draft');
		formData.append('title', title);
		formData.append('content', content);
		formData.append('category', category);
		formData.append('series', series);
		formData.append('division', division);
		formData.append('description', description);
		formData.append('status', 'draft');

		if (lastDraftId) {
			formData.append('post_id', lastDraftId);
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
			if (response.success) {
				lastDraftId = response.data.post_id;
				var element = document.getElementById("draftAlert");
				element.classList.remove("d-none");

				location.reload();
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
</script>