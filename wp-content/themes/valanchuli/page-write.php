<?php
/**
 * Template Name: Write Story Page
 */
get_header(); ?>

<div class="container mt-5">
	<form id="write-story-form" enctype="multipart/form-data">
		<!-- Step 1 -->
		<div id="step-1">
			<div class="row d-flex justify-content-center align-items-center p-2 p-lg-5">
				<div class="col-lg-6 col-12">
					<div class="d-flex justify-content-center align-items-center">
						<img src="<?php echo get_template_directory_uri() . '/images/story-write.png'; ?>" alt="Login Image" style="width: 60%;" />
					</div>
				</div>
				<div class="col-lg-6 col-12 mt-3 mt-lg-0 p-3 p-lg-5 bg-white">
					<!-- Title -->
					<div class="mb-3">
						<label class="form-label">தலைப்பு</label>
						<input type="text" class="form-control tamilwriter" id="story-title" required>
					</div>

					<!-- Category -->
					<div class="mb-3">
						<label class="form-label">வகை</label>
						<select class="form-select" id="story-category" required>
							<option value="">Select Category</option>
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

					<div class="mb-3 dropdown">
						<label for="category_dropdown_input" class="form-label">தொடர்கதை</label>

						<input type="text" readonly class="form-control dropdown-toggle form-select" id="story-series"
							name="story-series" data-bs-toggle="dropdown" value="தொடர்கதை அல்ல">

						<ul class="dropdown-menu w-100 p-2" id="category_dropdown">
							<li>
								<input type="text" class="form-control mb-2" id="series_input"
									placeholder="Type to create or search...">
							</li>
							<div id="category_list"></div>
						</ul>
					</div>

					<div class="mb-3 d-none" id="divisionDropdown">
						<label class="form-label">பிரிவுகள்</label>
						<select class="form-select" id="story-division" name="sotry-division">
							<option value="">Select Division</option>
							<option value="division1">Division 1</option>
							<option value="division2">Division 2</option>
							<option value="division3">Division 3</option>
						</select>
					</div>

					<!-- Image Upload -->
					<div class="mb-3">
						<label class="form-label">படம்</label>
						<input type="file" class="form-control" id="story-image" accept="image/*">
					</div>

					<button type="button" class="btn btn-secondary" id="next-step"><i class="fa-solid fa-arrow-right"></i>&nbsp;
						அடுத்தது</button>
				</div>
			</div>
		</div>

		<!-- Step 2 -->
		<div id="step-2" style="display: none;">
			<div class="mb-3">
				<label class="form-label">படைப்பை சேர்க்கவும்</label>
				<textarea id="story-content" class="form-control tamilwriter" rows="6"></textarea>
				<div id="tamil-suggestions" class="accordion mt-2"></div>
			</div>

			<button type="button" class="btn btn-secondary me-2" id="prev-step"><i class="fa-solid fa-arrow-left"></i>&nbsp;
				முந்தையது</button>
			<button type="submit" class="btn btn-primary me-2"><i class="fa-solid fa-floppy-disk"></i>&nbsp;
				சமர்ப்பிக்க</button>
			<button type="button" class="btn btn-primary" id="saveDraft"><i class="fa-solid fa-floppy-disk"></i>&nbsp;
				Save Draft</button>
		</div>
	</form>
	<div id="save-result" class="mt-3"></div>


	<div id="save-result" class="mt-3"></div>
</div>

<?php get_footer(); ?>

<script>
	jQuery(document).ready(function ($) {
		// $("#story-content").trumbowyg({
		// 	btns: [
		// 		['formatting'],
		// 		['fontsize'],
		// 		["bold", "italic", "underline"],
		// 		['justifyLeft', 'justifyCenter', 'justifyRight'],
		// 		['unorderedList', 'orderedList'],
		// 		["emoji"]
		// 	],
		// 	autogrow: true
		// });

		$('#next-step').on('click', function () {
			// Validate basic fields before proceeding
			// if (!$('#story-title').val() || !$('#story-category').val() || !$('#story-series').val()) {
			// 	alert("தயவு செய்து அனைத்து விவரங்களையும் நிரப்பவும்.");
			// 	return;
			// }

			$('#step-1').hide();
			$('#step-2').show();

			// Initialize Trumbowyg editor
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
				});
			}

			$('.trumbowyg-editor').css({
				height: '80rem',
				overflow: 'auto'
			});
		});

		$('#prev-step').on('click', function () {
			$('#step-2').hide();
			$('#step-1').show();
		});

		$('#story-content').on('tbwchange', function () {
			console.log("Trumbowyg content changed");
			clearTimeout(autoSaveTimeout);
			autoSaveTimeout = setTimeout(autoSaveDraft, 2000);
		});

		$('#saveDraft').click(function() {
			autoSaveDraft();
		});
	});
	document.addEventListener('DOMContentLoaded', function () {

		const categoryInput = document.getElementById('series_input');
		const selectedInput = document.getElementById('story-series');
		const categoryList = document.getElementById('category_list');

		// const categoryObj = <?php echo json_encode($series_names, JSON_UNESCAPED_UNICODE); ?>;
		// const categories = Object.values(categoryObj);

		const staticSeries = <?php echo json_encode($static_series, JSON_UNESCAPED_UNICODE); ?>;
		const dynamicSeries = <?php echo json_encode($dynamic_series, JSON_UNESCAPED_UNICODE); ?>;

		const labelMap = {
			...staticSeries,
			...dynamicSeries
		};

		const categories = Object.values(staticSeries).concat(Object.values(dynamicSeries));

		// categories = ['Adventure', 'Romance', 'Horror', 'Sci-Fi', 'Comedy'];

		function updateList(filter = '') {
			categoryList.innerHTML = '';
			const filtered = categories.filter(cat => cat.toLowerCase().includes(filter.toLowerCase()));
			if (filtered.length > 0) {
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
				item.querySelector('a').addEventListener('click', function (e) {
					e.preventDefault();
					selectedInput.value = filter;
					categoryInput.value = '';
					selectedInput.focus();
				});
				categoryList.appendChild(item);
			}
		}

		categoryInput.addEventListener('input', function () {
			updateList(this.value);
		});

		selectedInput.addEventListener('click', function () {
			const dropdown = document.getElementById('category_dropdown');
			dropdown.addEventListener('click', function (e) {
				const selectedValue = e.target.textContent.trim();
				// selectedInput.value = selectedValue;

				if (selectedValue !== "தொடர்கதை அல்ல") {
					var element = document.getElementById("divisionDropdown");
					element.classList.remove("d-none");
				} else {
					var element = document.getElementById("divisionDropdown");
					element.classList.add("d-none");
				}
			});

			setTimeout(() => {
				categoryInput.focus();
				updateList('');
			}, 150);
		});
	});


	//   tamil typing start
	//   tamil typing end





	// document.getElementById('story-series').addEventListener('change', function () {
	//     const newSeriesInput = document.getElementById('new-series');
	//     newSeriesInput.classList.toggle('d-none', this.value !== 'new');
	// });

	// document.getElementById('story-content').addEventListener('input', function () {
	//     const input = this.value;
	//     const lastWord = input.split(" ").pop();

	//     if (lastWord.toLowerCase() === 'vanakkam') {
	//         document.getElementById('tamil-suggestions').innerHTML =
	//             'Did you mean: <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="replaceLastWord(\'வணக்கம்\')">வணக்கம்</button>';
	//     } else {
	//         document.getElementById('tamil-suggestions').innerHTML = '';
	//     }
	// });

	// function replaceLastWord(tamilWord) {
	//     const textarea = document.getElementById('story-content');
	//     const words = textarea.value.trim().split(" ");
	//     words.pop();
	//     words.push(tamilWord);
	//     textarea.value = words.join(" ");
	//     document.getElementById('tamil-suggestions').innerHTML = '';
	// }

	document.getElementById('write-story-form').addEventListener('submit', function (e) {
		e.preventDefault();

		const title = document.getElementById('story-title').value;
		const category = document.getElementById('story-category').value;
		const series = document.getElementById('story-series').value;
		const division = (series != "தொடர்கதை அல்ல") ? document.getElementById('story-division').value : '';
		const content = document.getElementById('story-content').value;
		const imageInput = document.getElementById('story-image');

		// Validate series selection (not the default empty option)
		if (series === "") {
			alert('Please select a series option or "Not Series".');
			return;
		}

		const formData = new FormData();
		formData.append('action', 'save_story');
		formData.append('title', title);
		formData.append('category', category);
		formData.append('series', series);
		formData.append('division', division);
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
				document.getElementById('save-result').innerHTML = response.success
					? `<div class="alert alert-success">${response.data}</div>`
					: `<div class="alert alert-danger">${response.data}</div>`;
			});
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
		const imageInput = document.getElementById('story-image');

		// Skip if title and content are empty
		if (!title && !content) return;

		const formData = new FormData();
		formData.append('action', 'save_draft'); // Keep same AJAX hook
		formData.append('title', title);
		formData.append('content', content);
		formData.append('category', category);
		formData.append('series', series);
		formData.append('division', division);
		formData.append('status', 'draft');

		if (lastDraftId) {
			formData.append('post_id', lastDraftId);
		}

		// Optional: only append image if selected and not already uploaded
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
				document.getElementById('save-result').innerHTML =
					`<div class="alert alert-info">Auto-saved at ${new Date().toLocaleTimeString()}</div>`;
			}
		});
	}

	// let lastDraftId = null;
	fetch('<?php echo admin_url("admin-ajax.php?action=get_last_draft_story"); ?>')
		.then(res => res.json())
		.then(response => {
			if (response.success && response.data) {
				const data = response.data;

				lastDraftId = data.draft_id;
				document.getElementById('story-title').value = data.title || '';
				document.getElementById('story-content').value = data.content || '';
				if (data.category) {
					document.getElementById('story-category').value = data.category;
				}
				if (data.series) {
					document.getElementById('story-series').value = data.series;
				}
				if (data.series && data.series !== 'தொடர்கதை அல்ல') {
					document.getElementById('divisionDropdown').classList.remove('d-none');
					document.getElementById('story-division').value = data.division || '';
				}
				if (data.image_url) {
					const imgPreview = document.createElement('img');
					imgPreview.src = data.image_url;
					imgPreview.alt = "Uploaded Image Preview";
					imgPreview.style.maxWidth = "100px";
					document.getElementById('story-image').parentElement.appendChild(imgPreview);
				}
			}
		});
</script>