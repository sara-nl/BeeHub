This is the central **development portal** of *BeeHub*, [SARA](http://www.sara.nl/)'s new file sharing service. BeeHub's **user portal** can be found at http://www.beehub.nl/.

This repository contains a number of different code-bases, that are so close coupled that I didn't think they should live in separate git repositories.

*   **WebDAV:** the (BeeHub independent) custom WebDAV protocol implementation, as well as the BeeHub-specific XFS backend implementation.
*   **WebSite**: the [BeeHub user portal](http://www.beehub.nl/), built on [WordPress](http://www.wordpress.org/).
*   **WebClient**: the [BeeHub web client](http://beehub.nl/), built on [ExtJS](http://www.sencha.com/).

Each of these codebases has its own [milestones](/pieterb/BeeHub/issues/milestones) in this project.