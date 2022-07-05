(function ($) {
	$.fn.liveUpdate = function (list, line) {
		return this.each(function () {
			new $.liveUpdate(this, list, line);
		});
	};

	$.liveUpdate = function (e, list, line) {
		this.field = $(e);
		this.line = line;
		this.list = $(list);

		if (this.list.length > 0) {
			this.init();
		} else {
			$(e).hide();
		}
	};

	$.liveUpdate.prototype = {
		init:function () {
			var self = this;
			this.setupCache();

			this.field.on('keyup keypress', function (e) {
				self.filter(e);
			});
			self.filter();
			this.field.closest('form').off('keyup keypress');
		},

		filter:function (e) {
			var input_text = this.field.val();
			if ($.trim(input_text) == '') {
				this.list.find(this.line).show();
				this.field.focus();
			} else {
				this.displayResults(this.getScores(input_text.toLowerCase()));
			}
		},

		setupCache:function () {
			var self = this;
			this.rows = [];
			this.cache = [];
			this.list.find(this.line).each(function () {

				var item = {
					txt : $(this).find('.il-item-title').text().toLowerCase(),
				};

				self.rows.push($(this));
				self.cache.push(item);

			});
			this.cache_length = this.cache.length;
		},

		displayResults:function (scores) {
			var self = this;

			this.list.find(this.line).hide();
			$.each(scores, function (i, score) {
				self.rows[score.index].show();
			});
		},

		getScores:function (term) {
			var scores = [];
			for (var i = 0; i < this.cache_length; i++) {
				var score = this.cache[i].txt.score(term, 1);
				if (score > 0) {

					scores.push({score : score, index : i});
				}
			}

			return scores;
		}
	}
})(jQuery);