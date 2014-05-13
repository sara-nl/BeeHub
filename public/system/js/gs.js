if (nl.sara.beehub.gs === undefined) {
  /** @namespace Groups and sponsors */
  nl.sara.beehub.gs = {};
}

if (nl.sara.beehub.gs.view === undefined) {
  /** @namespace Groups and sponsors */
  nl.sara.beehub.gs.view = {};
}

// After load
$(function () {
  var view = new nl.sara.beehub.gs.view.GroupsSponsorsView();
});