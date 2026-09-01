# My Enrolled Courses

A Moodle Dashboard block that gives students and staff a single, sortable, show/hide-able list of everything they're enrolled in — with one-click access to each course's activities, right from the block.

[![Moodle Plugin CI](https://github.com/dualcube/moodle-block_my_enrolled_courses/actions/workflows/moodle-plugin-ci.yml/badge.svg)](https://github.com/dualcube/moodle-block_my_enrolled_courses/actions/workflows/moodle-plugin-ci.yml)
![Moodle](https://img.shields.io/badge/Moodle-5.0%20--%205.3-orange.svg)
[![License: GPL v3](https://img.shields.io/badge/license-GPLv3-blue.svg)](http://www.gnu.org/copyleft/gpl.html)

Maintained by [DualCube](https://dualcube.com).

## Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Setup](#setup)
- [Usage](#usage)
- [Uninstall](#uninstall)
- [License](#license)

## Features

- **One place for every course.** Lists all of a user's enrolled courses in a single Dashboard block.
- **Drag-and-drop ordering.** Hold the 4-way arrow next to a course and drag it to reorder the list — your order is remembered.
- **Show/hide per course.** Tuck away courses you're done with, and bring them back any time, without unenrolling.
- **Inline activity list.** Click the **+** next to a course to expand its activities without leaving the Dashboard.
- **One click to the course.** Every course title links straight to its course page.

## Requirements

| | |
|---|---|
| **Moodle** | 5.0 or later (tested through 5.3) |
| **PHP** | Whatever the target Moodle version requires |

## Installation

**Option 1 — Plugin installer (recommended)**

1. Go to *Site administration ▸ Plugins ▸ Install plugins*.
2. Upload or drag & drop the plugin ZIP file.
3. Follow the on-screen upgrade prompts.

**Option 2 — Manual**

1. Extract the plugin into `<moodle-root>/blocks/my_enrolled_courses`.
2. Visit `/admin/index.php` in your browser to complete the install.

## Setup

The block lives on the Dashboard (`/my/`) page.

1. Turn on the Dashboard's **Edit mode** ("Customise this page").
2. Click **Add a block ▸ My enrolled courses**.

To make it appear on every user's Dashboard automatically, see *Site administration ▸ Appearance ▸ Default Dashboard page* — add the block there, then use **Reset Dashboard for all users**.

## Usage

| Action | How |
|---|---|
| Open a course | Click its title in the block |
| See a course's activities | Click the **+** next to the course |
| Reorder courses | Drag by the 4-way arrow on the left of a course |
| Hide / show courses | Click **Show/Hide courses** in the block footer, select courses, then **Hide course** or **Show course** |

## Uninstall

*Site administration ▸ Plugins ▸ Plugins overview ▸ My enrolled courses ▸ Uninstall*.

## License

[GNU GPL v3 or later](http://www.gnu.org/copyleft/gpl.html)
