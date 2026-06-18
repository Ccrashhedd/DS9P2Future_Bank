package com.example.project2.ui.components

import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.clickable
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.unit.dp
import com.example.project2.ui.theme.GlassBorder
import com.example.project2.ui.theme.GlassSurface
import com.example.project2.ui.theme.PrimaryPurple
import com.example.project2.ui.theme.SilverDark
import com.example.project2.ui.theme.TextPrimary

data class BankBottomItem(
    val key: String,
    val label: String,
    val icon: ImageVector
)

@Composable
fun BankBottomBar(
    items: List<BankBottomItem>,
    selectedKey: String,
    onSelected: (String) -> Unit,
    modifier: Modifier = Modifier
) {
    Surface(
        modifier = modifier
            .fillMaxWidth()
            .padding(horizontal = 14.dp, vertical = 10.dp),
        color = GlassSurface,
        shape = RoundedCornerShape(26.dp),
        border = BorderStroke(1.dp, GlassBorder)
    ) {
        Row(
            modifier = Modifier.padding(6.dp),
            horizontalArrangement = Arrangement.SpaceBetween
        ) {
            items.forEach { item ->
                val selected = item.key == selectedKey
                Surface(
                    modifier = Modifier
                        .weight(1f)
                        .height(50.dp)
                        .clickable { onSelected(item.key) },
                    color = if (selected) PrimaryPurple.copy(alpha = 0.22f) else Color.Transparent,
                    shape = RoundedCornerShape(20.dp)
                ) {
                    Row(
                        horizontalArrangement = Arrangement.Center,
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Icon(
                            imageVector = item.icon,
                            contentDescription = item.label,
                            tint = if (selected) TextPrimary else SilverDark
                        )
                        if (selected) {
                            Text(
                                text = item.label,
                                color = TextPrimary,
                                style = MaterialTheme.typography.labelMedium,
                                modifier = Modifier.padding(start = 6.dp)
                            )
                        }
                    }
                }
            }
        }
    }
}
