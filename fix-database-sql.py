#!/usr/bin/env python3
"""
Fix database.sql by splitting large INSERT statements
"""

def fix_database_sql():
    print("🔧 Fixing database.sql file...")
    
    try:
        with open('database.sql', 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Split the massive INSERT into smaller chunks
        lines = content.split('\n')
        fixed_lines = []
        
        for i, line in enumerate(lines):
            if i == 332:  # Line 333 (0-indexed)
                print(f"📍 Found problematic line {i+1}")
                
                # Check if it's a large INSERT statement
                if 'INSERT INTO' in line and len(line) > 10000:
                    print(f"⚠️  Large INSERT found: {len(line)} characters")
                    
                    # Split the VALUES part
                    if 'VALUES' in line:
                        parts = line.split('VALUES')
                        table_part = parts[0] + 'VALUES'
                        values_part = parts[1]
                        
                        # Split values into smaller chunks
                        values = values_part.split('),(')
                        chunk_size = 10  # 10 records per INSERT
                        
                        for j in range(0, len(values), chunk_size):
                            chunk = values[j:j+chunk_size]
                            if j == 0:
                                chunk[0] = chunk[0].lstrip('(')
                            if j + chunk_size >= len(values):
                                chunk[-1] = chunk[-1].rstrip(');') + ';'
                            else:
                                chunk[-1] = chunk[-1] + ');'
                            
                            new_line = table_part + ' (' + '),('.join(chunk)
                            fixed_lines.append(new_line)
                    else:
                        # If no VALUES, just add the line as-is
                        fixed_lines.append(line)
                else:
                    fixed_lines.append(line)
            else:
                fixed_lines.append(line)
        
        # Write fixed content
        with open('database_fixed.sql', 'w', encoding='utf-8') as f:
            f.write('\n'.join(fixed_lines))
        
        print("✅ Fixed database saved as 'database_fixed.sql'")
        print("📊 Original lines:", len(lines))
        print("📊 Fixed lines:", len(fixed_lines))
        
    except Exception as e:
        print(f"❌ Error: {e}")

if __name__ == "__main__":
    fix_database_sql()