if (nl.sara.beehub.gss === undefined) {
  /** @namespace Groups and sponsors */
  nl.sara.beehub.gss = {};
}

if (nl.sara.beehub.gss.view === undefined) {
  /** @namespace Groups and sponsors */
  nl.sara.beehub.gss.view = {};
}

// After load
$(function () {
  var view = new nl.sara.beehub.gss.view.GroupsSponsorsView();
});