# Packing List Management System

This system allows users to track both group and personal items for a trip, ensuring everything gets packed correctly.

## Features

1. **Group Packing List**
   - Track items that need to be brought by the group
   - Assign items to specific group members
   - Track who's bringing what
   - Search functionality
   - Only Duy can delete items

2. **Personal Packing List**
   - Each member can maintain their own personal packing list
   - Check off items as they're packed
   - Add/remove personal items
   - Search functionality

## Technical Information

### Data Storage

The application uses JSON files for data storage:

- `/public/data/packing_group.json` - Stores all group items and assigned carriers
- `/public/data/packing_personal.json` - Stores personal items for each group member

### API Endpoints

API file: `/public/api/packing.php`

Available endpoints:

1. **GET** `?action=get_group_items`
   - Returns all group items

2. **GET** `?action=get_personal_items&member=<name>`
   - Returns personal items for a specific member
   - If no member specified, returns all personal items

3. **POST** `?action=add_group_item`
   - Body: `{ "name": "Item name", "carrier": "Member name" }`
   - Adds a new group item with initial carrier

4. **POST** `?action=add_personal_item`
   - Body: `{ "name": "Item name", "member": "Member name" }`
   - Adds a new personal item for the member

5. **POST** `?action=update_group_item`
   - Body: `{ "id": "itemId", "member": "Member name", "action": "add_carrier|remove_carrier|delete" }`
   - Update group item carriers or delete an item

6. **POST** `?action=update_personal_item`
   - Body: `{ "id": "itemId", "member": "Member name", "action": "toggle_packed|delete" }`
   - Toggle packed status or delete a personal item

## Styling

The UI follows the modern style defined in:
- `/public/css/style.css` - Main site styles
- `/public/css/packing.css` - Packing list specific styles
