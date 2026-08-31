/**
 * Type-to-search dropdown for picking the account to merge with.
 *
 * Two modes, chosen server side by the size of the installation. Without a `searchUrl` the whole
 * candidate list travels with the page and is filtered here: accents are stripped and every
 * whitespace separated term has to match the name or the e-mail, which makes "nov jan" find
 * "Jan Novák". With a `searchUrl` the page only carries the first page of candidates and every
 * keystroke searches the server instead, so a large user table never ends up in the HTML.
 *
 * Either way only a candidate the user actually picked reaches the hidden input, so the form can
 * never submit an account the list has not offered.
 */
export default (candidates = [], searchUrl = null, limit = 50) => ({
    candidates,
    searchUrl,
    limit,
    search: '',
    selected: null,
    isOpen: false,
    loading: false,
    active: 0,

    /**
     * Ignores responses of searches the user has already typed past.
     */
    request: 0,
    debounce: null,

    get filtered() {
        if (this.searchUrl) {
            return this.candidates;
        }

        const terms = this.normalize(this.search).split(/\s+/).filter(Boolean);

        if (! terms.length) {
            return this.candidates;
        }

        return this.candidates.filter((candidate) => {
            const haystack = this.normalize(candidate.name + ' ' + candidate.email);

            return terms.every((term) => haystack.includes(term));
        });
    },

    get visible() {
        return this.filtered.slice(0, this.limit);
    },

    get hasHidden() {
        return this.filtered.length >= this.limit;
    },

    normalize(value) {
        return value.toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    },

    open() {
        this.isOpen = true;
    },

    close() {
        this.isOpen = false;
    },

    onInput() {
        this.selected = null;
        this.active = 0;
        this.open();

        if (this.searchUrl) {
            this.searchRemote();
        }
    },

    /**
     * Debounced, so holding a key down does not queue a request per character.
     */
    searchRemote() {
        clearTimeout(this.debounce);
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
        this.open();

        if (! this.visible.length) {
            return;
        }

        this.active = (this.active + step + this.visible.length) % this.visible.length;

        this.$nextTick(() => {
            this.$el.querySelector('.dropdown-item.active')?.scrollIntoView({ block: 'nearest' });
        });
    },

    choose(candidate) {
        if (! candidate) {
            return;
        }

        this.selected = candidate;
        this.search = candidate.name;
        this.close();
    },

    chooseActive() {
        this.choose(this.visible[this.active]);
    },
});
