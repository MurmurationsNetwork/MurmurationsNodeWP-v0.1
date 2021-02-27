**NB This Wordpress Plugin was designed to work with version 0.1 of Murmurations - We aim to develop an update to work with [version 1.0](https://github.com/MurmurationsNetwork/MurmurationsProtocol), please [get in touch](https://murmurations.network/contact/) if you can help.**


# Murmurations

This is the repository for the Murmurations node plugin.

### Objective
Help organisations working for a more just and regenerative world easily publish information about themselves that can be aggregated into feeds, maps, and directories to make movements visible.

### Current environment and challenges
Across many sectors, work has been done to map nodes of social, economic, or ecological change. Maps and directories make it easier for those working for a better world to find each other, and make it easier for individuals to find businesses, organisations, and information sources that align with their values. Existing maps and directories include regional, national, and global maps and directories of co-ops and other solidarity economy projects, ecovillage and intentional community directories, and maps of social justice and social change groups, independent media organizations and aggregators, organic and regenerative agriculture directories, etc.

The typical approach to building a directory or map is to collect information about nodes and add it to a database that is owned and designed by the aggregator. Once established, the aggregators either solicit additions and updates from nodes, manually curate the node records to add new ones and delete or update obsolete ones, or simply leave the database to gradually become obsolete.

Several challenges impact this work, including:

 * Large amount of manual effort required to identify and enter the information for many small organizations within the target sector/region
 * Difficulty sharing data between similar or overlapping maps, directories, or aggregators
 * Difficulty keeping data up to date
 * Requirement for a single node to update data on multiple platforms, each with their own set of fields, authentication requirements, etc.
 * Fragmented, incomplete data making it more difficult for individuals to find the nodes or news of interest within a sector/region
 
### Murmurations approach
Our approach is different. Rather than siloed collections of data held by aggregators, we aim to make it extremely easy for nodes to host data about themselves on their own web sites. This data is structured in a way that it can be automatically "crawled" and aggregated, based on specific filter criteria, so that directories, aggregators, and maps can automatically keep data about a node up to date from a single authoritative source. That authoritative source is the node itself.
 
Example potential use cases

 * National co-op association member map and news feed
 * Ecovillage events listings
 * Feed of regenerative agriculture news and events in Vancouver

### Features 

**Single point of maintenance**
Rather than a node having to log in and add or update their data on many different platforms, data can be updated in one place, which is the admin interface of their own web site.

**Granular visibility without redundant effort**
Using a consistent protocol and allowing filtering means that, from the same underlying data, one aggregator could, for example, create a map of organic farms in the UK, and another could create a news feed for regenerative agriculture projects near the city of Stroud. One directory could show solidarity economy organizations in Europe, while another could show only worker co-ops in Spain.

**Making use of existing components**
The plugin will make use of existing solutions for machine-readable data, including RSS, RDF, JSON-LD, and FOAF-like specifications that are already in use and have been developed and refined by structured data experts. We don't want to reinvent the wheel.

**Network emergence**
Making it easy for aligned organizations and individuals to find each other.

