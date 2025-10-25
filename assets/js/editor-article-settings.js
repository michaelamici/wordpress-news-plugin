/* global wp */
(function() {
	const { registerPlugin } = wp.plugins || {};
	const { PluginDocumentSettingPanel } = wp.editPost || {};
	const { ToggleControl, TextControl, __experimentalInputControl: InputControl } = wp.components || {};
	const { useSelect, useDispatch } = wp.data || {};
	const { useMemo, createElement } = wp.element || {};
	const { __ } = wp.i18n || ((s)=>s);

	if (!registerPlugin || !PluginDocumentSettingPanel) {
		return;
	}

	function ArticleSettingsPanel() {
		const selector = ( select ) => {
			const editor = select('core/editor');
			return {
				postType: editor.getCurrentPostType(),
				postId: editor.getCurrentPostId(),
				meta: editor.getEditedPostAttribute('meta') || {},
			};
		};
		const { postType, postId, meta } = useSelect(selector, []);
		const { editPost } = useDispatch('core/editor');
		const isNews = postType === 'news';
		const currentMeta = useMemo(() => meta || {}, [meta]);
		if (!isNews) return null;
		const setMeta = (key, value) => editPost({ meta: { ...currentMeta, [key]: value } });

		return createElement(
			PluginDocumentSettingPanel,
			{ title: __('Article Settings', 'news'), initialOpen: true },
			[
				createElement(ToggleControl, {
					label: __('Featured', 'news'),
					checked: !!currentMeta._news_featured,
					onChange: (v) => setMeta('_news_featured', !!v),
				}),
				createElement(ToggleControl, {
					label: __('Breaking', 'news'),
					checked: !!currentMeta._news_breaking,
					onChange: (v) => setMeta('_news_breaking', !!v),
				}),
				createElement(ToggleControl, {
					label: __('Exclusive', 'news'),
					checked: !!currentMeta._news_exclusive,
					onChange: (v) => setMeta('_news_exclusive', !!v),
				}),
				createElement(ToggleControl, {
					label: __('Sponsored', 'news'),
					checked: !!currentMeta._news_sponsored,
					onChange: (v) => setMeta('_news_sponsored', !!v),
				}),
				createElement(ToggleControl, {
					label: __('Live', 'news'),
					checked: !!currentMeta._news_is_live,
					onChange: (v) => setMeta('_news_is_live', !!v),
				}),
				createElement(InputControl || TextControl, {
					label: __('Last Updated', 'news'),
					type: 'datetime-local',
					value: currentMeta._news_last_updated || '',
					onChange: (v) => setMeta('_news_last_updated', v),
				}),
				createElement(TextControl, {
					label: __('Byline', 'news'),
					value: currentMeta._news_byline || '',
					onChange: (v) => setMeta('_news_byline', v),
				}),
			]
		);
	}

	registerPlugin('news-article-settings', { render: ArticleSettingsPanel });
})();


