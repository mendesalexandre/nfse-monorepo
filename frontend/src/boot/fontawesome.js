import { defineBoot } from "#q-app/wrappers";
import { Quasar } from "quasar";
import "../assets/fontawesome/css/all.min.css";

const faIconSet = {
  name: "fontawesome-light",
  type: {
    positive: "fa-light",
    negative: "fa-light",
    info: "fa-light",
    warning: "fa-light",
  },
  arrow: {
    up: "fa-light fa-chevron-up",
    right: "fa-light fa-chevron-right",
    down: "fa-light fa-chevron-down",
    left: "fa-light fa-chevron-left",
    dropdown: "fa-light fa-chevron-down",
  },
  chevron: {
    left: "fa-light fa-chevron-left",
    right: "fa-light fa-chevron-right",
  },
  pullToRefresh: {
    icon: "fa-light fa-arrow-down",
  },
  carousel: {
    left: "fa-light fa-chevron-left",
    right: "fa-light fa-chevron-right",
    up: "fa-light fa-chevron-up",
    down: "fa-light fa-chevron-down",
    navigationIcon: "fa-light fa-circle",
  },
  chip: {
    remove: "fa-light fa-xmark",
    selected: "fa-light fa-check",
  },
  datetime: {
    arrowLeft: "fa-light fa-chevron-left",
    arrowRight: "fa-light fa-chevron-right",
    now: "fa-light fa-clock",
    today: "fa-light fa-calendar-day",
  },
  editor: {
    bold: "fa-light fa-bold",
    italic: "fa-light fa-italic",
    strikethrough: "fa-light fa-strikethrough",
    underline: "fa-light fa-underline",
    unorderedList: "fa-light fa-list-ul",
    orderedList: "fa-light fa-list-ol",
    subscript: "fa-light fa-subscript",
    superscript: "fa-light fa-superscript",
    hyperlink: "fa-light fa-link",
    toggleFullscreen: "fa-light fa-expand",
    quote: "fa-light fa-quote-right",
    left: "fa-light fa-align-left",
    center: "fa-light fa-align-center",
    right: "fa-light fa-align-right",
    justify: "fa-light fa-align-justify",
    print: "fa-light fa-print",
    outdent: "fa-light fa-outdent",
    indent: "fa-light fa-indent",
    removeFormat: "fa-light fa-eraser",
    formatting: "fa-light fa-heading",
    fontSize: "fa-light fa-text-height",
    align: "fa-light fa-align-left",
    hr: "fa-light fa-minus",
    undo: "fa-light fa-rotate-left",
    redo: "fa-light fa-rotate-right",
    heading: "fa-light fa-heading",
    code: "fa-light fa-code",
    size: "fa-light fa-text-height",
    font: "fa-light fa-font",
    viewSource: "fa-light fa-code",
  },
  expansionItem: {
    icon: "fa-light fa-chevron-down",
    denseIcon: "fa-light fa-chevron-down",
  },
  fab: {
    icon: "fa-light fa-plus",
    activeIcon: "fa-light fa-xmark",
  },
  field: {
    clear: "fa-light fa-xmark-circle",
    error: "fa-light fa-circle-exclamation",
  },
  pagination: {
    first: "fa-light fa-chevrons-left",
    prev: "fa-light fa-chevron-left",
    next: "fa-light fa-chevron-right",
    last: "fa-light fa-chevrons-right",
  },
  rating: {
    icon: "fa-light fa-star",
  },
  stepper: {
    done: "fa-light fa-check",
    active: "fa-light fa-pen",
    error: "fa-light fa-triangle-exclamation",
  },
  tabs: {
    left: "fa-light fa-chevron-left",
    right: "fa-light fa-chevron-right",
    up: "fa-light fa-chevron-up",
    down: "fa-light fa-chevron-down",
  },
  table: {
    arrowUp: "fa-light fa-arrow-up-long",
    warning: "fa-light fa-triangle-exclamation",
    firstPage: "fa-light fa-chevrons-left",
    prevPage: "fa-light fa-chevron-left",
    nextPage: "fa-light fa-chevron-right",
    lastPage: "fa-light fa-chevrons-right",
  },
  tree: {
    icon: "fa-light fa-play",
  },
  uploader: {
    done: "fa-light fa-check",
    clear: "fa-light fa-xmark",
    add: "fa-light fa-plus",
    upload: "fa-light fa-cloud-arrow-up",
    removeQueue: "fa-light fa-broom",
    removeUploaded: "fa-light fa-broom",
  },
};

export default defineBoot(() => {
  Quasar.iconSet.set(faIconSet);
});
