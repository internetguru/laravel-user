/**
 * Type-to-search list of accounts that can be merged into this one.
 *
 * Two modes, chosen server side by the size of the installation. Without a `searchUrl` the whole
 * candidate list travels with the page and is filtered here: accents are stripped and every
 * whitespace separated term has to match the name or the e-mail, which makes "nov jan" find
 * "Jan Novák". With a `searchUrl` the page carries nothing and every keystroke searches the
 * server instead, so a large user table never ends up in the HTML.
 *
 * A candidate list that already fits in `shown` rows is listed straight away — there is nothing
 * to search through, and the page renders no search box for it. Otherwise nothing is listed
 * until something is typed, and a query matching more than `shown` accounts asks for a narrower
 * one rather than paginating: picking the right account out of a long list is what the search is
 * there to avoid.
 */
export default (candidates = [], searchUrl = null, shown = 10) => ({
    candidates,
    searchUrl,
    shown,
    search: '',
    loading: false,
    active: 0,

    /**
     * Ignores responses of searches the user has already typed past.
     */
    request: 0,
    debounce: null,

    /**
     * True when the whole candidate list fits, so it is shown without searching.
     */
    get listsEverything() {
        return ! this.searchUrl && this.candidates.length <= this.shown;
    },

    get matches() {
        if (this.listsEverything) {
            return this.candidates;
        }

        if (! this.search.trim().length) {
            return [];
        }

        if (this.searchUrl) {
            return this.candidates;
        }

        const terms = this.normalize(this.search).split(/\s+/).filter(Boolean);

        return this.candidates.filter((candidate) => {
            const haystack = this.normalize(candidate.name + ' ' + candidate.email);

            return terms.every((term) => haystack.includes(term));
        });
    },

    get tooMany() {
        return this.matches.length > this.shown;
    },

    get visible() {
        // While a search is in flight the box shows the spinner alone, never a stale result
        return this.loading || this.tooMany ? [] : this.matches;
    },

    /**
     * Which of the notes the list shows in place of results, if any.
     */
    get note() {
        if (this.loading) {
            return 'loading';
        }

        if (this.tooMany) {
            return 'more';
        }

        if (this.visible.length) {
            return null;
        }

        return this.search.trim().length ? 'none' : 'hint';
    },

    normalize(value) {
        return value.toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    },

    onInput() {
        this.active = 0;

        if (this.searchUrl) {
            this.searchRemote();
        }
    },

    /**
     * Debounced, so holding a key down does not queue a request per character.
     */
    searchRemote() {
        clearTimeout(this.debounce);

        if (! this.search.trim().length) {
            this.candidates = [];
            this.loading = false;

            return;
        }

        this.loading = true;

        this.debounce = setTimeout(() => {
            const request = ++this.request;

            fetch(this.searchUrl + '?q=' + encodeURIComponent(this.search), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
                .then((response) => (response.ok ? response.json() : []))
                .catch(() => [])
                .then((results) => {
                    // A slower earlier search must not overwrite a newer result
                    if (request !== this.request) {
                        return;
                    }

                    this.candidates = results;
                    this.active = 0;
                    this.loading = false;
                });
        }, 200);
    },

    move(step) {
        if (! this.visible.length) {
            return;
        }

        this.active = (this.active + step + this.visible.length) % this.visible.length;

        this.$nextTick(() => {
            this.$el.querySelector('.merge-candidate.active')?.scrollIntoView({ block: 'nearest' });
        });
    },

    addActive() {
        this.$el.querySelector('[data-index="' + this.active + '"]')?.click();
    },
});
